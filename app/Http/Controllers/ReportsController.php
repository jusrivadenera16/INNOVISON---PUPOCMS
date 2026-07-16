<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MedicalConditions;
use App\Models\Category;
use App\Models\Consultation;
use App\Models\AppointmentFeedback;
use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\HealthProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReportsController extends Controller
{
    private function formatInventoryQuantity(float $value): string
    {
        if (abs($value - round($value)) < 0.00001) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function inventoryReportCategoryLabel(Item $item): string
    {
        if ($item->category === 'Medicine') {
            if (!empty($item->medicine_type)) {
                return 'Medicine (' . $item->medicine_type . ')';
            }
        }

        return (string) $item->category;
    }

    private function consumedStockQuantityForItem(Item $item, float $consumedTotal): float
    {
        return $item->convertDispensingQuantityToStockQuantity($consumedTotal);
    }

    private function parseReportDate(string $value, Carbon $fallback): Carbon
    {
        $value = trim($value);

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date;
                }
            } catch (\Throwable $exception) {
                // Try the next supported input format.
            }
        }

        return $fallback->copy();
    }

    private function applyHealthApprovalDateRange($query, Carbon $dateFrom, Carbon $dateTo)
    {
        return $query->where(function ($builder) use ($dateFrom, $dateTo) {
            $builder->whereBetween('verified_at', [$dateFrom, $dateTo])
                ->orWhere(function ($fallback) use ($dateFrom, $dateTo) {
                    $fallback->whereNull('verified_at')
                        ->whereBetween('created_at', [$dateFrom, $dateTo]);
                });
        });
    }

    private function healthApprovalDate(?HealthProfile $profile): ?Carbon
    {
        if (!$profile) {
            return null;
        }

        return $profile->verified_at ?? $profile->created_at;
    }

    private function healthApprovalDateSql(): string
    {
        return 'COALESCE(verified_at, created_at)';
    }

    private function normalizeHealthProfileNumber($value): ?float
    {
        $normalized = preg_replace('/[^0-9.]/', '', (string) $value);

        if ($normalized === '') {
            return null;
        }

        return (float) $normalized;
    }

    private function healthProfileBmi(HealthProfile $profile): ?float
    {
        $height = $this->normalizeHealthProfileNumber($profile->height ?: optional($profile->user)->height);
        $weight = $this->normalizeHealthProfileNumber($profile->weight ?: optional($profile->user)->weight);

        if (!$height || !$weight) {
            return null;
        }

        $heightMeters = $height > 3 ? $height / 100 : $height;

        if ($heightMeters <= 0) {
            return null;
        }

        return round($weight / ($heightMeters * $heightMeters), 1);
    }

    private function healthProfileBmiCategory(?float $bmi): string
    {
        if ($bmi === null) {
            return 'no_bmi';
        }

        return match (true) {
            $bmi < 18.5 => 'underweight',
            $bmi < 25 => 'normal',
            $bmi < 30 => 'overweight',
            default => 'obese',
        };
    }

    private function healthProfileBmiCategoryLabel(?float $bmi): string
    {
        return match ($this->healthProfileBmiCategory($bmi)) {
            'underweight' => 'Underweight',
            'normal' => 'Normal',
            'overweight' => 'Overweight',
            'obese' => 'Obese',
            default => 'No BMI recorded',
        };
    }

    private function healthProfileTextValue($value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->filter(fn ($item) => trim((string) $item) !== '')
                ->implode(', ');
        }

        return trim((string) $value);
    }

    private function healthProfileConditionText(HealthProfile $profile, string $source = 'all'): string
    {
        $source = in_array($source, ['all', 'health_form', 'final_review', 'remarks'], true) ? $source : 'all';

        $healthFormParts = [
            $profile->has_disability === 'Yes' ? 'Disability: ' . ($this->healthProfileTextValue($profile->disability_type) ?: 'Yes') : '',
            $profile->has_illness === 'Yes' ? 'Illness: Yes' : '',
            $this->healthProfileTextValue($profile->medical_history),
            $this->healthProfileTextValue($profile->other_illness),
            $this->healthProfileTextValue($profile->food_allergies),
            $this->healthProfileTextValue($profile->medicine_allergies),
            $this->healthProfileTextValue($profile->other_med_allergies),
        ];

        $finalReviewParts = [
            $this->healthProfileTextValue($profile->physical_assessment_status),
            $this->healthProfileTextValue($profile->medical_condition_remarks),
            $this->healthProfileTextValue($profile->med_assessment_remarks),
        ];

        $remarksParts = [
            $this->healthProfileTextValue($profile->assessment_remarks),
            $this->healthProfileTextValue($profile->med_assessment_remarks),
            $this->healthProfileTextValue($profile->medical_condition_remarks),
            $this->healthProfileTextValue($profile->pending_reason),
            $this->healthProfileTextValue($profile->encode_remarks),
        ];

        $parts = match ($source) {
            'health_form' => $healthFormParts,
            'final_review' => $finalReviewParts,
            'remarks' => $remarksParts,
            default => array_merge($healthFormParts, $finalReviewParts, $remarksParts),
        };

        return collect($parts)
            ->map(fn ($part) => trim((string) $part))
            ->filter(fn ($part) => $part !== '' && $part !== '[]')
            ->unique()
            ->implode(' | ');
    }

    private function healthConditionKeywordSuggestions(int $limit = 18): Collection
    {
        return HealthProfile::query()
            ->latest('updated_at')
            ->limit(250)
            ->get([
                'medical_history',
                'other_illness',
                'food_allergies',
                'medicine_allergies',
                'other_med_allergies',
                'medical_condition_remarks',
                'assessment_remarks',
                'med_assessment_remarks',
                'pending_reason',
                'encode_remarks',
            ])
            ->flatMap(function (HealthProfile $profile) {
                return [
                    $this->healthProfileTextValue($profile->medical_history),
                    $this->healthProfileTextValue($profile->other_illness),
                    $this->healthProfileTextValue($profile->food_allergies),
                    $this->healthProfileTextValue($profile->medicine_allergies),
                    $this->healthProfileTextValue($profile->other_med_allergies),
                    $this->healthProfileTextValue($profile->medical_condition_remarks),
                    $this->healthProfileTextValue($profile->assessment_remarks),
                    $this->healthProfileTextValue($profile->med_assessment_remarks),
                    $this->healthProfileTextValue($profile->pending_reason),
                    $this->healthProfileTextValue($profile->encode_remarks),
                ];
            })
            ->flatMap(function ($value) {
                return preg_split('/[|;,\\r\\n]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            })
            ->map(function ($value) {
                $value = trim(preg_replace('/\s+/', ' ', (string) $value));
                $value = preg_replace('/^(?:Medical Condition|Condition|Remarks?|Reason|Pending Reason|Nurse Remarks|Assessment Remarks|Medical Assessment Remarks|Final Review Remarks)\s*:\s*/i', '', $value) ?? $value;

                return trim(Str::limit(trim($value, " \t\n\r\0\x0B-:"), 44, ''));
            })
            ->filter(fn ($value) => $value !== '' && $value !== '[]' && mb_strlen($value) >= 3)
            ->reject(function ($value) {
                $normalized = mb_strtolower(trim((string) $value));
                $plain = preg_replace('/[^a-z0-9]+/', '', $normalized) ?? $normalized;

                if (in_array($normalized, ['yes', 'none', 'n/a', 'na', 'normal', 'no findings', 'no restriction'], true)) {
                    return true;
                }

                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', (string) $value)
                    || preg_match('/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/', (string) $value)) {
                    return true;
                }

                if (preg_match('/\bdr\.?\b|\bdoctor\b/i', (string) $value)
                    || preg_match('/\bOD\s*:\s*\d+.*\bOS\s*:\s*\d+/i', (string) $value)) {
                    return true;
                }

                if (in_array($plain, ['normalpe', 'normalpefindings', 'normalpr', 'normalfindings', 'nofindings', 'norestriction'], true)) {
                    return true;
                }

                return str_contains($normalized, 'normal')
                    && preg_match('/\b(p\.?e\.?|pr|findings?)\b/i', (string) $value);
            })
            ->unique(fn ($value) => mb_strtolower($value))
            ->take($limit)
            ->values();
    }

    public function digitalLogbook(Request $request)
    {
        return view('admin.reports.digital-logbook');
    }

    public function dailyTreatmentRecord(Request $request)
    {
        $dateFrom = $this->parseReportDate(
            (string) $request->query('date_from', ''),
            now()->startOfMonth()
        )->startOfDay();
        $dateTo = $this->parseReportDate(
            (string) $request->query('date_to', ''),
            now()
        )->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $consultations = Consultation::query()
            ->with([
                'user.healthProfile',
                'medicalCondition.category',
                'medicineItem',
                'attendingStaff.adminProfile',
            ])
            ->whereBetween('consultation_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('consultation_date')
            ->orderBy('time_in')
            ->orderBy('created_at')
            ->get();

        return view('admin.reports.daily-treatment-record', compact(
            'dateFrom',
            'dateTo',
            'consultations',
        ));
    }

    public function appointmentStatistics(Request $request)
    {
        $legacyMonth = trim((string) $request->query('month', ''));
        $monthFrom = trim((string) $request->query('month_from', $legacyMonth ?: now()->format('Y-m')));
        $monthTo = trim((string) $request->query('month_to', $legacyMonth ?: $monthFrom));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthFrom)) {
            $monthFrom = now()->format('Y-m');
        }

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthTo)) {
            $monthTo = $monthFrom;
        }

        $monthStart = Carbon::createFromFormat('Y-m-d', $monthFrom . '-01')->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m-d', $monthTo . '-01')->endOfMonth();

        if ($monthStart->gt($monthEnd)) {
            [$monthStart, $monthEnd] = [
                Carbon::createFromFormat('Y-m-d', $monthTo . '-01')->startOfMonth(),
                Carbon::createFromFormat('Y-m-d', $monthFrom . '-01')->endOfMonth(),
            ];
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $filters = [
            'month_from' => $monthFrom,
            'month_to' => $monthTo,
            'patient_type' => $this->normalizeAppointmentStatsFilter((string) $request->query('patient_type', ''), ['student', 'faculty', 'admin', 'dependent']),
            'status' => $this->normalizeAppointmentStatsFilter((string) $request->query('status', ''), ['pending', 'approved', 'completed', 'cancelled', 'expired', 'missed']),
            'service' => $this->normalizeAppointmentStatsFilter((string) $request->query('service', ''), ['general_consultation', 'blood_pressure_monitoring']),
            'source' => $this->normalizeAppointmentStatsFilter((string) $request->query('source', ''), ['online', 'walk-in']),
        ];

        $appointmentRows = Appointment::query()
            ->with('user')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->map(function (Appointment $appointment) {
                $source = $this->appointmentStatsSource($appointment->type ?? null, 'online');

                return [
                    'date' => Carbon::parse($appointment->date),
                    'time' => $appointment->time ? Carbon::parse($appointment->time) : null,
                    'patient_type' => $this->appointmentStatsPatientType($appointment->user_type ?: optional($appointment->user)->user_type ?: optional($appointment->user)->user_role),
                    'status' => $this->appointmentStatsStatus($appointment->status),
                    'service' => $this->appointmentStatsService($appointment->service ?: null),
                    'source' => $source,
                    'reason' => trim((string) ($appointment->problem ?: $appointment->notes ?: '')),
                ];
            });

        $allRows = $appointmentRows
            ->sortBy(fn ($row) => $row['date']->timestamp)
            ->values();
        $rows = $allRows
            ->when($filters['patient_type'] !== '', fn ($collection) => $collection->where('patient_type', $filters['patient_type']))
            ->when($filters['status'] !== '', fn ($collection) => $collection->where('status', $filters['status']))
            ->when($filters['service'] !== '', fn ($collection) => $collection->where('service', $filters['service']))
            ->when($filters['source'] !== '', fn ($collection) => $collection->where('source', $filters['source']))
            ->values();

        $totalAppointments = $rows->count();
        $uniqueDayCount = max(1, $monthStart->diffInDays($monthEnd) + 1);
        $averagePerDay = $totalAppointments / $uniqueDayCount;

        $summaryCards = [
            ['label' => 'Total Records', 'value' => $totalAppointments, 'hint' => 'Filtered activity'],
            ['label' => 'Online', 'value' => $rows->where('source', 'online')->count(), 'hint' => 'Online appointment requests'],
            ['label' => 'Walk-in', 'value' => $rows->where('source', 'walk-in')->count(), 'hint' => 'Same-day clinic visits'],
            ['label' => 'Average Appointments / Day', 'value' => number_format($averagePerDay, 1), 'hint' => 'Across selected range'],
        ];

        $statusBreakdown = $this->appointmentStatsBreakdown($rows, 'status', [
            'pending', 'approved', 'completed', 'cancelled', 'expired', 'missed',
        ]);
        $patientTypeBreakdown = $this->appointmentStatsBreakdown($rows, 'patient_type', [
            'student', 'faculty', 'admin', 'dependent',
        ]);
        $sourceBreakdown = $this->appointmentStatsBreakdown($rows, 'source', ['online', 'walk-in']);
        $topReasons = $this->appointmentStatsBreakdown($rows, 'reason')->take(5)->values();
        $serviceBreakdown = $this->appointmentStatsBreakdown($rows, 'service')->take(5)->values();

        $peakHours = $rows
            ->filter(fn ($row) => $row['time'] instanceof Carbon)
            ->groupBy(fn ($row) => $row['time']->format('g A'))
            ->map(fn ($items, $hour) => ['label' => $hour, 'value' => $items->count()])
            ->sortByDesc('value')
            ->take(5)
            ->values();

        $groupByMonth = $monthStart->diffInDays($monthEnd) > 62;
        $today = now()->startOfDay();

        if ($groupByMonth) {
            $trendRows = $rows
                ->groupBy(fn ($row) => $row['date']->format('M Y'))
                ->map(fn ($items, $label) => ['label' => $label, 'value' => $items->count()])
                ->values();

            if ($trendRows->isEmpty()) {
                $trendRows = collect([['label' => $monthStart->format('M Y'), 'value' => 0]]);
            }
        } else {
            $anchorDate = $today->betweenIncluded($monthStart->copy()->startOfDay(), $monthEnd->copy()->startOfDay())
                ? $today
                : $monthEnd->copy()->startOfDay();

            $trendRows = collect([2, 1, 0])
                ->map(function (int $daysAgo) use ($rows, $anchorDate) {
                    $date = $anchorDate->copy()->subDays($daysAgo);

                    return [
                        'label' => $date->format('M d'),
                        'value' => $rows->filter(fn ($row) => $row['date']->isSameDay($date))->count(),
                        'row_class' => $daysAgo === 0 ? 'is-current' : 'is-muted',
                    ];
                })
                ->values();
        }

        $trendTotal = $trendRows->sum('value');

        return view('admin.reports.appointment-statistics', compact(
            'filters',
            'monthStart',
            'monthEnd',
            'summaryCards',
            'statusBreakdown',
            'patientTypeBreakdown',
            'sourceBreakdown',
            'topReasons',
            'serviceBreakdown',
            'peakHours',
            'trendRows',
            'trendTotal',
            'totalAppointments',
            'averagePerDay'
        ));
    }

    private function normalizeAppointmentStatsFilter(string $value, array $allowed): string
    {
        $value = strtolower(trim($value));

        return in_array($value, $allowed, true) ? $value : '';
    }

    private function appointmentStatsPatientType(?string $value): string
    {
        return match (strtolower(trim((string) $value))) {
            'faculty' => 'faculty',
            'admin', 'staff', 'regular' => 'admin',
            'guest', 'dependent', 'dependents' => 'dependent',
            default => 'student',
        };
    }

    private function appointmentStatsService(?string $value): string
    {
        $service = strtolower(trim((string) $value));

        if (str_contains($service, 'blood') || str_contains($service, 'bp')) {
            return 'blood_pressure_monitoring';
        }

        return 'general_consultation';
    }

    private function appointmentStatsSource(?string $value, string $default = 'online'): string
    {
        $source = strtolower(trim((string) $value));

        if ($source === '') {
            return $default;
        }

        return in_array($source, ['walkin', 'walk-in', 'walk in'], true) ? 'walk-in' : 'online';
    }

    private function appointmentStatsStatus(?string $value): string
    {
        $status = strtolower(trim((string) $value));

        return match ($status) {
            'approved' => 'approved',
            'completed', 'complete' => 'completed',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'missed' => 'missed',
            default => 'pending',
        };
    }

    private function appointmentStatsBreakdown(Collection $rows, string $key, array $preferredOrder = []): Collection
    {
        $items = $rows
            ->map(fn ($row) => trim((string) ($row[$key] ?? '')))
            ->filter()
            ->countBy()
            ->map(fn ($count, $label) => [
                'label' => $this->appointmentStatsLabel($label),
                'raw_label' => $label,
                'value' => $count,
            ]);

        if ($preferredOrder !== []) {
            return collect($preferredOrder)
                ->map(fn ($label) => $items->get($label, [
                    'label' => $this->appointmentStatsLabel($label),
                    'raw_label' => $label,
                    'value' => 0,
                ]))
                ->values();
        }

        return $items->sortByDesc('value')->values();
    }

    private function appointmentStatsLabel(string $value): string
    {
        return (string) Str::of($value)
            ->replace(['-', '_'], ' ')
            ->title();
    }

    private function normalizeReportPatientType(?string $value): ?string
    {
        return match (strtolower(trim((string) $value))) {
            'student' => 'student',
            'faculty' => 'faculty',
            'admin', 'staff' => 'admin',
            'dependent', 'dependents' => 'dependent',
            default => null,
        };
    }

    private function normalizeReportGender(?string $value): ?string
    {
        $gender = strtolower(trim((string) $value));

        return match ($gender) {
            'male' => 'male',
            'female' => 'female',
            default => null,
        };
    }

    private function emptyGadTable(): array
    {
        $row = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0, 'total' => 0];

        return [
            'female' => $row,
            'male' => $row,
            'pwd_male' => $row,
            'pwd_female' => $row,
            'senior_male' => $row,
            'senior_female' => $row,
            'total' => $row,
        ];
    }

    private function incrementGadRow(array &$table, string $rowKey, string $patientType): void
    {
        $table[$rowKey][$patientType]++;
        $table[$rowKey]['total']++;
    }

    private function addGadEntry(array &$table, string $patientType, ?string $gender, bool $isPwd, bool $isSenior): void
    {
        $this->incrementGadRow($table, 'total', $patientType);

        if ($gender === 'female') {
            $this->incrementGadRow($table, 'female', $patientType);
        } elseif ($gender === 'male') {
            $this->incrementGadRow($table, 'male', $patientType);
        }

        if ($isPwd && $gender === 'male') {
            $this->incrementGadRow($table, 'pwd_male', $patientType);
        }

        if ($isPwd && $gender === 'female') {
            $this->incrementGadRow($table, 'pwd_female', $patientType);
        }

        if ($isSenior && $gender === 'male') {
            $this->incrementGadRow($table, 'senior_male', $patientType);
        }

        if ($isSenior && $gender === 'female') {
            $this->incrementGadRow($table, 'senior_female', $patientType);
        }
    }

    private function combineGadTables(array ...$tables): array
    {
        $combined = $this->emptyGadTable();

        foreach ($tables as $table) {
            foreach ($combined as $rowKey => $row) {
                foreach (array_keys($row) as $columnKey) {
                    $combined[$rowKey][$columnKey] += (int) ($table[$rowKey][$columnKey] ?? 0);
                }
            }
        }

        return $combined;
    }

    private function resolveConsultationUser(?Consultation $consultation): ?User
    {
        static $cache = [];

        if (!$consultation) {
            return null;
        }

        $userId = $consultation->user_id ?? null;
        if ($userId) {
            $cacheKey = 'id:' . $userId;
            if (!array_key_exists($cacheKey, $cache)) {
                $cache[$cacheKey] = User::with('healthProfile')->find($userId);
            }

            return $cache[$cacheKey];
        }

        $name = trim((string) $consultation->name);
        $role = $this->normalizeReportPatientType($consultation->user_role ?: $consultation->user_type ?: '');
        $cacheKey = 'name:' . strtolower($name) . '|' . $role;

        if (!array_key_exists($cacheKey, $cache)) {
            $matches = User::with('healthProfile')
                ->where('name', $name)
                ->get();

            if ($matches->count() > 1 && $role !== null) {
                $filtered = $matches->filter(function (User $user) use ($role) {
                    return $this->normalizeReportPatientType($user->user_role ?? $user->user_type ?? '') === $role;
                })->values();

                $cache[$cacheKey] = $filtered->count() === 1 ? $filtered->first() : $matches->first();
            } else {
                $cache[$cacheKey] = $matches->first();
            }
        }

        return $cache[$cacheKey];
    }

    private function extractUserDemographics(?User $user): array
    {
        $gender = $this->normalizeReportGender(
            $user?->gender
            ?: optional($user?->healthProfile)->sex
        );

        $birthday = trim((string) (
            $user?->DOB
            ?: optional($user?->healthProfile)->birthday
        ));

        $isSenior = false;
        if ($birthday !== '') {
            try {
                $isSenior = Carbon::parse($birthday)->age >= 60;
            } catch (\Throwable $e) {
                $isSenior = false;
            }
        }

        $isPwd = trim((string) optional($user?->healthProfile)->has_disability) === 'Yes';

        return compact('gender', 'isSenior', 'isPwd');
    }

    private function buildConsultationGadTable(Collection $consultations): array
    {
        $table = $this->emptyGadTable();

        foreach ($consultations as $consultation) {
            $patientType = $this->normalizeReportPatientType($consultation->user_role ?: $consultation->user_type ?: '');
            if ($patientType === null) {
                continue;
            }

            $user = $this->resolveConsultationUser($consultation);
            $demographics = $this->extractUserDemographics($user);

            $this->addGadEntry(
                $table,
                $patientType,
                $demographics['gender'],
                $demographics['isPwd'],
                $demographics['isSenior']
            );
        }

        return $table;
    }

    private function buildAppointmentGadTable(Collection $appointments): array
    {
        $table = $this->emptyGadTable();

        foreach ($appointments as $appointment) {
            $patientType = $this->normalizeReportPatientType($appointment->user_type ?? '');
            if ($patientType === null) {
                continue;
            }

            $user = $appointment->user;
            if ($user && !$user->relationLoaded('healthProfile')) {
                $user->load('healthProfile');
            }

            $demographics = $this->extractUserDemographics($user);

            $this->addGadEntry(
                $table,
                $patientType,
                $demographics['gender'],
                $demographics['isPwd'],
                $demographics['isSenior']
            );
        }

        return $table;
    }

    private function buildMarGadTables(Collection $categories, Carbon $dateFrom, Carbon $dateTo): array
    {
        $consultations = $categories->flatMap(function ($category) {
            return $category->medicalConditions->flatMap->consultations;
        })->unique('id')->values();

        $certificateConsultations = $consultations->filter(function ($consultation) {
            return in_array(trim((string) ($consultation->certificate_type ?? 'none')), ['excused_letter', 'coc_ijt', 'coc_ladderized'], true);
        })->values();

        $onlineAppointments = Appointment::with('user.healthProfile')
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('type', 'online')
            ->where('status', '!=', 'Cancelled')
            ->get();

        $consultationTable = $this->buildConsultationGadTable($consultations);
        $certificateTable = $this->buildConsultationGadTable($certificateConsultations);
        $triageOnlineTable = $this->buildAppointmentGadTable($onlineAppointments);

        return [
            'consultation' => $consultationTable,
            'certificate' => $certificateTable,
            'triage_online' => $triageOnlineTable,
            'combined' => $this->combineGadTables($consultationTable, $certificateTable, $triageOnlineTable),
        ];
    }

    public function appointmentHistory(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $users = collect();
        $consultations = collect();
        $selectedUser = null;
        $summary = [
            'total' => 0,
            'last_visit' => null,
            'common_complaint' => null,
            'has_condition' => false,
            'condition_details' => [],
        ];

        if ($search !== '') {
            $users = User::where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                })
                ->select('id', 'first_name', 'middle_name', 'last_name', 'name', 'email', 'course', 'year', 'section', 'student_number', 'student_id', 'user_type', 'user_role')
                ->limit(20)
                ->get();

            $userId = $request->query('user_id');
            if ($userId) {
                $selectedUser = User::find($userId);
                $appointments = Appointment::where('user_id', $userId)
                    ->with(['user'])
                    ->orderByDesc('date')
                    ->orderByDesc('time')
                    ->get();

                $consultationRecords = Consultation::where('user_id', $userId)
                    ->with(['user', 'attendingStaff'])
                    ->orderByDesc('consultation_date')
                    ->orderByDesc('time_in')
                    ->get();

                $usedConsultationIds = [];
                $appointmentRows = $appointments->map(function ($appointment) use ($consultationRecords, &$usedConsultationIds) {
                    $matches = $consultationRecords->filter(function ($c) use ($appointment, $usedConsultationIds) {
                        if (in_array($c->id, $usedConsultationIds, true)) {
                            return false;
                        }
                        if (!$appointment->date || !$c->consultation_date) {
                            return false;
                        }
                        $appointmentDate = \Carbon\Carbon::parse($appointment->date)->format('Y-m-d');
                        $consultationDate = \Carbon\Carbon::parse($c->consultation_date)->format('Y-m-d');
                        return $appointmentDate === $consultationDate;
                    });
                    $consultation = $matches->sortBy(function ($c) use ($appointment) {
                        if (!$appointment->time || !$c->time_in) {
                            return PHP_INT_MAX;
                        }
                        try {
                            $appointmentTime = \Carbon\Carbon::parse($appointment->time);
                            $consultationTime = \Carbon\Carbon::parse($c->time_in);
                            return abs($consultationTime->diffInSeconds($appointmentTime, false));
                        } catch (\Throwable $e) {
                            return PHP_INT_MAX;
                        }
                    })->first();

                    if ($consultation) {
                        $usedConsultationIds[] = $consultation->id;
                    }

                    return (object) [
                        'appointment' => $appointment,
                        'consultation' => $consultation,
                        'sort_date' => $appointment->date,
                        'sort_time' => $consultation->time_in ?? $appointment->time,
                    ];
                });

                $consultationRows = $consultationRecords
                    ->reject(fn ($consultation) => in_array($consultation->id, $usedConsultationIds, true))
                    ->map(function ($consultation) {
                        return (object) [
                            'appointment' => null,
                            'consultation' => $consultation,
                            'sort_date' => optional($consultation->consultation_date)->format('Y-m-d'),
                            'sort_time' => $consultation->time_in,
                        ];
                    });

                $consultations = $appointmentRows
                    ->merge($consultationRows)
                    ->sortByDesc(fn ($record) => trim((string) $record->sort_date) . ' ' . trim((string) $record->sort_time))
                    ->values();

                $summaryConsultations = $consultations->filter(fn ($record) => $record->consultation);
                $healthProfile = HealthProfile::query()
                    ->where('user_id', $userId)
                    ->latest()
                    ->first();
                $conditionDetails = collect([
                        'Disability' => $healthProfile?->has_disability === 'Yes'
                            ? ($healthProfile->disability_type ?: 'Yes')
                            : null,
                        'Illness' => $healthProfile?->has_illness === 'Yes'
                            ? (is_array($healthProfile->medical_history) ? implode(', ', array_filter($healthProfile->medical_history)) : $healthProfile?->medical_history)
                            : null,
                        'Other Illness' => $healthProfile?->other_illness,
                        'Food Allergies' => $healthProfile?->food_allergies,
                        'Medicine Allergies' => is_array($healthProfile?->medicine_allergies)
                            ? implode(', ', array_filter($healthProfile->medicine_allergies))
                            : $healthProfile?->medicine_allergies,
                        'Other Medicine Allergies' => $healthProfile?->other_med_allergies,
                        'Nurse Remarks' => $healthProfile?->medical_condition_remarks,
                    ])
                    ->filter(fn ($value) => trim((string) $value) !== '' && trim((string) $value) !== '[]')
                    ->all();
                $summary = [
                    'total' => $summaryConsultations->count(),
                    'last_visit' => optional($summaryConsultations->first()?->consultation?->consultation_date)->format('M d, Y'),
                    'common_complaint' => $summaryConsultations
                        ->map(fn ($record) => trim((string) ($record->consultation->reason_for_visit ?? $record->consultation->comments ?? '')))
                        ->filter()
                        ->countBy()
                        ->sortDesc()
                        ->keys()
                        ->first(),
                    'has_condition' => $healthProfile?->hasMedicalCondition() ?? false,
                    'condition_details' => $conditionDetails,
                ];
            }
        }

        return view('admin.reports.appointment-history', compact(
            'search',
            'users',
            'consultations',
            'selectedUser',
            'summary'
        ));
    }

    public function printAppointmentHistory(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            abort(404, 'User ID not provided');
        }

        $user = User::findOrFail($userId);

        $appointments = Appointment::where('user_id', $userId)
            ->with(['user'])
            ->orderByDesc('date')
            ->get();

        $consultationRecords = Consultation::where('user_id', $userId)
            ->with(['user', 'attendingStaff'])
            ->orderByDesc('consultation_date')
            ->orderByDesc('time_in')
            ->get();

        $usedConsultationIds = [];
        $appointmentRows = $appointments->map(function ($appointment) use ($consultationRecords, &$usedConsultationIds) {
            $matches = $consultationRecords->filter(function ($c) use ($appointment, $usedConsultationIds) {
                if (in_array($c->id, $usedConsultationIds, true)) {
                    return false;
                }
                if (!$appointment->date || !$c->consultation_date) {
                    return false;
                }
                $appointmentDate = \Carbon\Carbon::parse($appointment->date)->format('Y-m-d');
                $consultationDate = \Carbon\Carbon::parse($c->consultation_date)->format('Y-m-d');
                return $appointmentDate === $consultationDate;
            });
            $consultation = $matches->sortBy(function ($c) use ($appointment) {
                if (!$appointment->time || !$c->time_in) {
                    return PHP_INT_MAX;
                }
                try {
                    $appointmentTime = \Carbon\Carbon::parse($appointment->time);
                    $consultationTime = \Carbon\Carbon::parse($c->time_in);
                    return abs($consultationTime->diffInSeconds($appointmentTime, false));
                } catch (\Throwable $e) {
                    return PHP_INT_MAX;
                }
            })->first();

            if ($consultation) {
                $usedConsultationIds[] = $consultation->id;
            }

            return (object) [
                'appointment' => $appointment,
                'consultation' => $consultation,
                'sort_date' => $appointment->date,
                'sort_time' => $consultation->time_in ?? $appointment->time,
            ];
        });

        $consultationRows = $consultationRecords
            ->reject(fn ($consultation) => in_array($consultation->id, $usedConsultationIds, true))
            ->map(function ($consultation) {
                return (object) [
                    'appointment' => null,
                    'consultation' => $consultation,
                    'sort_date' => optional($consultation->consultation_date)->format('Y-m-d'),
                    'sort_time' => $consultation->time_in,
                ];
            });

        $consultations = $appointmentRows
            ->merge($consultationRows)
            ->sortByDesc(fn ($record) => trim((string) $record->sort_date) . ' ' . trim((string) $record->sort_time))
            ->values();

        $output = $request->query('output', 'pdf');

        if ($output === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.print-appointment-history', [
                'user' => $user,
                'consultations' => $consultations,
                'isPdf' => true,
            ])->setPaper('a4', 'landscape');

            return $pdf->stream(
                'appointment-history-' . $user->name . '-' . now()->format('YmdHis') . '.pdf',
                ['Attachment' => false]
            );
        }

        return view('admin.reports.print-appointment-history', [
            'user' => $user,
            'consultations' => $consultations,
            'isPdf' => false,
        ]);
    }

    public function healthFormsReport(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $dateFrom = $this->parseReportDate((string) $request->query('date_from', ''), now()->startOfMonth())->startOfDay();
        $dateTo = $this->parseReportDate((string) $request->query('date_to', ''), now())->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $issuedBaseQuery = HealthProfile::query()
            ->with('user')
            ->whereIn('clearance_status', ['Issued', 'Fully Cleared']);

        if ($search !== '') {
            $issuedBaseQuery->where(function ($builder) use ($search) {
                $builder->where('course_college', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('course', 'like', "%{$search}%");
                    });
            });
        }

        $this->applyHealthApprovalDateRange($issuedBaseQuery, $dateFrom, $dateTo);

        $pendingBaseQuery = HealthProfile::query()
            ->with('user')
            ->whereNotIn('clearance_status', ['Issued', 'Fully Cleared', 'Rejected'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($search !== '') {
            $pendingBaseQuery->where(function ($builder) use ($search) {
                $builder->where('course_college', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('course', 'like', "%{$search}%");
                    });
            });
        }

        $pendingByCourse = (clone $pendingBaseQuery)
            ->get()
            ->groupBy(function (HealthProfile $form) {
                $course = trim((string) ($form->course_college ?: optional($form->user)->course ?: 'Unspecified Course'));
                return $course !== '' ? $course : 'Unspecified Course';
            })
            ->map(fn ($forms) => $forms->count());

        $issuedFormsCollection = (clone $issuedBaseQuery)
            ->get()
            ->groupBy(function (HealthProfile $form) {
                $course = trim((string) ($form->course_college ?: optional($form->user)->course ?: 'Unspecified Course'));
                return $course !== '' ? $course : 'Unspecified Course';
            })
            ->map(function ($forms, $course) use ($pendingByCourse) {
                $sortedForms = $forms->sortByDesc(function (HealthProfile $form) {
                    return $this->healthApprovalDate($form);
                })->values();

                $withConditionCount = $forms->filter(fn (HealthProfile $form) => $form->hasMedicalCondition())->count();
                $issuedCount = $forms->count();

                return (object) [
                    'course' => $course,
                    'issued_count' => $issuedCount,
                    'with_condition_count' => $withConditionCount,
                    'no_condition_count' => $issuedCount - $withConditionCount,
                    'for_approval_count' => (int) ($pendingByCourse->get($course) ?? 0),
                    'last_issued_at' => $this->healthApprovalDate($sortedForms->first()),
                ];
            })
            ->sortByDesc('issued_count')
            ->values();

        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $issuedFormsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $issuedForms = new LengthAwarePaginator(
            $currentItems,
            $issuedFormsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        $summaryQuery = HealthProfile::query()->whereIn('clearance_status', ['Issued', 'Fully Cleared']);

        $totalIssued = (clone $summaryQuery)->count();
        $totalCourses = (clone $summaryQuery)
            ->with('user:id,course')
            ->get()
            ->map(function (HealthProfile $form) {
                return trim((string) ($form->course_college ?: optional($form->user)->course));
            })
            ->filter()
            ->unique()
            ->count();
        $issuedWithConditions = (clone $summaryQuery)->withMedicalCondition()->count();
        $topCourse = optional($issuedFormsCollection->first())->course ?? 'No course data yet';

        $allCourses = HealthProfile::distinct('course_college')
            ->pluck('course_college')
            ->filter()
            ->sort()
            ->values();

        return view('admin.reports.health-forms', compact(
            'issuedForms',
            'totalIssued',
            'totalCourses',
            'issuedWithConditions',
            'search',
            'dateFrom',
            'dateTo',
            'allCourses'
        ));
    }

    private function healthFormsApplicantsListQuery(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $courseFilter = trim((string) $request->query('course', ''));
        $userTypeFilter = trim((string) $request->query('type', ''));
        $genderFilter = trim((string) $request->query('gender', ''));
        $conditionFilter = trim((string) $request->query('condition', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $query = HealthProfile::query()
            ->with(['user', 'approvedBy', 'reviewStartedBy']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($courseFilter !== '') {
            $query->where(function ($q) use ($courseFilter) {
                $q->where('course_college', 'like', "%{$courseFilter}%")
                    ->orWhereHas('user', function ($uq) use ($courseFilter) {
                        $uq->where('course', 'like', "%{$courseFilter}%");
                    });
            });
        }

        if ($userTypeFilter !== '') {
            $query->whereHas('user', function ($q) use ($userTypeFilter) {
                $q->where('user_type', '=', $userTypeFilter);
            });
        }

        if ($genderFilter !== '') {
            $query->where(function ($q) use ($genderFilter) {
                $q->where('sex', '=', $genderFilter)
                    ->orWhereHas('user', function ($uq) use ($genderFilter) {
                        $uq->where('gender', '=', $genderFilter);
                    });
            });
        }

        if ($conditionFilter === 'yes') {
            $query->withMedicalCondition();
        } elseif ($conditionFilter === 'no') {
            $query->withoutMedicalCondition();
        }

        if ($statusFilter === 'approved') {
            $query->whereIn('clearance_status', ['Issued', 'Fully Cleared']);
        } elseif ($statusFilter === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('clearance_status')
                    ->orWhereIn('clearance_status', ['', 'Pending', 'For Verification', 'Pending/Conditional', 'Pending Resubmission']);
            });
        } elseif ($statusFilter === 'rejected') {
            $query->where('clearance_status', 'Rejected');
        }

        return [$query, $search, $courseFilter, $userTypeFilter, $genderFilter, $conditionFilter, $statusFilter];
    }

    public function healthFormsApplicantsList(Request $request)
    {
        [$query, $search, $courseFilter, $userTypeFilter, $genderFilter, $conditionFilter, $statusFilter] = $this->healthFormsApplicantsListQuery($request);

        $perPage = (string) $request->query('per_page', '20');
        if (!in_array($perPage, ['20', '40', '80', '100', 'all'], true)) {
            $perPage = '20';
        }

        $logbookQuery = $query->orderByDesc('created_at');
        $logbookRecords = $logbookQuery
            ->paginate($perPage === 'all' ? max(1, (clone $logbookQuery)->count()) : (int) $perPage)
            ->withQueryString();

        $courses = HealthProfile::query()
            ->select('course_college', 'course_code')
            ->whereNotNull('course_college')
            ->get()
            ->map(function (HealthProfile $profile) {
                $name = trim((string) $profile->course_college);
                $code = trim((string) $profile->course_code);

                return [
                    'name' => $name,
                    'code' => $code !== '' ? $code : $name,
                ];
            })
            ->filter(fn (array $course) => $course['name'] !== '')
            ->unique('name')
            ->sortBy('name')
            ->filter()
            ->values();

        return view('admin.reports.health-forms-applicants-list', compact(
            'logbookRecords',
            'courses',
            'search',
            'courseFilter',
            'userTypeFilter',
            'genderFilter',
            'conditionFilter',
            'statusFilter',
            'perPage'
        ));
    }

    private function healthFormsLogbookQuery(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $courseFilter = trim((string) $request->query('course', ''));
        $userTypeFilter = trim((string) $request->query('type', ''));
        $genderFilter = trim((string) $request->query('gender', ''));
        $conditionFilter = trim((string) $request->query('condition', ''));
        $statusFilter = 'approved';

        $query = HealthProfile::query()
            ->with(['user', 'approvedBy', 'reviewStartedBy'])
            ->whereIn('clearance_status', ['Issued', 'Fully Cleared']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($courseFilter !== '') {
            $query->where(function ($q) use ($courseFilter) {
                $q->where('course_college', 'like', "%{$courseFilter}%")
                  ->orWhereHas('user', function ($uq) use ($courseFilter) {
                      $uq->where('course', 'like', "%{$courseFilter}%");
                  });
            });
        }

        if ($userTypeFilter !== '') {
            $query->whereHas('user', function ($q) use ($userTypeFilter) {
                $q->where('user_type', '=', $userTypeFilter);
            });
        }

        if ($genderFilter !== '') {
            $query->whereHas('user', function ($q) use ($genderFilter) {
                $q->where('gender', '=', $genderFilter);
            });
        }

        if ($conditionFilter === 'yes') {
            $query->withMedicalCondition();
        } elseif ($conditionFilter === 'no') {
            $query->withoutMedicalCondition();
        }

        return [$query, $search, $courseFilter, $userTypeFilter, $genderFilter, $conditionFilter, $statusFilter];
    }

    public function healthFormsLogbook(Request $request)
    {
        $dateFrom = $this->parseReportDate(
            (string) $request->query('date_from', ''),
            now()->startOfMonth()
        )->startOfDay();
        $dateTo = $this->parseReportDate(
            (string) $request->query('date_to', ''),
            now()
        )->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $records = HealthProfile::query()
            ->with(['user', 'approvedBy', 'reviewStartedBy'])
            ->whereIn('clearance_status', ['Issued', 'Fully Cleared']);

        $this->applyHealthApprovalDateRange($records, $dateFrom, $dateTo);

        $records = $records
            ->orderBy(DB::raw($this->healthApprovalDateSql()))
            ->orderBy('created_at')
            ->get();

        return view('admin.reports.health_forms_logbook', compact('records', 'dateFrom', 'dateTo'));
    }

    public function exportHealthFormsLogbook(Request $request)
    {
        [$query] = $this->healthFormsLogbookQuery($request);

        $records = $query->orderByDesc('created_at')->get();
        $filename = 'health-forms-approval-logbook-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            echo "\xEF\xBB\xBF";
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Name',
                'Gender',
                'Course',
                'Type',
                'Submitted',
                'Time In',
                'Reviewed By',
                'Time Out',
                'Approved By',
                'Status',
                'Condition',
                'Medical Condition Details',
            ]);

            foreach ($records as $record) {
                $user = $record->user;
                $approver = $record->approvedBy;
                $reviewer = $record->reviewStartedBy;
                $isApproved = in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true);
                $hasCondition = $record->hasMedicalCondition();
                $formatList = static function ($value): string {
                    if (is_array($value)) {
                        return collect($value)
                            ->filter(fn ($item) => trim((string) $item) !== '')
                            ->implode(', ');
                    }

                    return trim((string) $value);
                };
                $conditionDetails = collect();

                if ($record->has_disability === 'Yes') {
                    $conditionDetails->push('Disability: ' . (trim((string) $record->disability_type) !== '' ? $record->disability_type : 'Yes'));
                }

                $medicalHistory = $formatList($record->medical_history);
                if ($record->has_illness === 'Yes' || $medicalHistory !== '') {
                    $conditionDetails->push('Medical History: ' . ($medicalHistory !== '' ? $medicalHistory : 'Yes'));
                }

                foreach ([
                    'Other Illness' => $record->other_illness,
                    'Food Allergies' => $record->food_allergies,
                    'Medicine Allergies' => $record->medicine_allergies,
                    'Other Medicine Allergies' => $record->other_med_allergies,
                    'Nurse Remarks' => $record->medical_condition_remarks,
                ] as $label => $value) {
                    $formattedValue = $formatList($value);
                    if ($formattedValue !== '' && $formattedValue !== '[]') {
                        $conditionDetails->push($label . ': ' . $formattedValue);
                    }
                }

                fputcsv($output, [
                    optional($user)->name ?: 'N/A',
                    optional($user)->gender ?: 'N/A',
                    $record->course_college ?: optional($user)->course ?: 'N/A',
                    optional($user)->user_type ?: 'N/A',
                    optional($record->created_at)->format('M d, Y g:i A') ?: 'N/A',
                    $record->review_started_at ? Carbon::parse($record->review_started_at)->format('M d, Y g:i A') : 'N/A',
                    optional($reviewer)->name ?: 'N/A',
                    $isApproved && $this->healthApprovalDate($record) ? $this->healthApprovalDate($record)->format('M d, Y g:i A') : 'N/A',
                    $isApproved ? (optional($approver)->name ?: 'N/A') : 'N/A',
                    $isApproved ? 'Approved' : 'Pending',
                    $hasCondition ? 'Yes' : 'No',
                    $hasCondition ? ($conditionDetails->isNotEmpty() ? $conditionDetails->implode(' | ') : 'Condition flagged, but no details provided.') : 'N/A',
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function feedbackReport(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $monthFilter = trim((string) $request->query('month', now()->format('Y-m')));

        $query = AppointmentFeedback::query()
            ->with(['appointment', 'user'])
            ->whereNotNull('submitted_at');

        if ($monthFilter !== '') {
            $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();
            $query->whereBetween('submitted_at', [$monthStart, $monthEnd]);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('feedback', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('appointment', function ($appointmentQuery) use ($search) {
                        $appointmentQuery->where('service', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%")
                            ->orWhere('user_type', 'like', "%{$search}%");
                    });
            });
        }

        $feedbackItems = (clone $query)
            ->latest('submitted_at')
            ->paginate(12)
            ->through(function (AppointmentFeedback $feedback) {
                $appointment = $feedback->appointment;
                $user = $feedback->user;
                $firstName = trim((string) ($user->first_name ?? ''));
                $lastName = trim((string) ($user->last_name ?? ''));
                $fallbackName = trim((string) ($user->name ?? ''));

                if ($firstName === '' && $fallbackName !== '') {
                    $nameParts = preg_split('/\s+/', $fallbackName) ?: [];
                    $firstName = trim((string) ($nameParts[0] ?? ''));
                    $lastName = trim((string) ($nameParts[count($nameParts) - 1] ?? ''));
                }

                $surnameInitial = $lastName !== '' ? strtoupper(substr($lastName, 0, 1)) . '.' : '';
                $displayName = trim($firstName . ($surnameInitial !== '' ? ' ' . $surnameInitial : ''));
                if ($displayName === '') {
                    $displayName = 'Clinic User';
                }

                $initials = collect(preg_split('/\s+/', $displayName) ?: [])
                    ->filter()
                    ->take(2)
                    ->map(fn ($part) => strtoupper(substr(rtrim($part, '.'), 0, 1)))
                    ->implode('');

                return (object) [
                    'id' => $feedback->id,
                    'name' => $displayName,
                    'initials' => $initials !== '' ? $initials : 'CU',
                    'student_number' => trim((string) ($user->student_number ?? '')),
                    'role' => trim((string) ($appointment->user_type ?? $user->user_role ?? 'User')),
                    'service' => trim((string) ($appointment->service ?? 'General Consultation')),
                    'appointment_type' => trim((string) ($appointment->type ?? '')),
                    'rating' => (int) $feedback->rating,
                    'score_out_of_ten' => number_format(((int) $feedback->rating) * 2, 1),
                    'message' => trim((string) $feedback->feedback),
                    'submitted_at' => $feedback->submitted_at,
                    'time_ago' => optional($feedback->submitted_at)->diffForHumans() ?? 'Recently',
                ];
            });

        $summaryBaseQuery = AppointmentFeedback::query()->whereNotNull('submitted_at');
        if ($monthFilter !== '') {
            $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();
            $summaryBaseQuery->whereBetween('submitted_at', [$monthStart, $monthEnd]);
        }

        $totalFeedbacks = (clone $summaryBaseQuery)->count();
        $averageRating = round((float) ((clone $summaryBaseQuery)->avg('rating') ?? 0), 1);
        $clinicScore = round($averageRating * 2, 1);
        $recommendedCount = (clone $summaryBaseQuery)->where('rating', '>=', 4)->count();
        $lowRatingCount = (clone $summaryBaseQuery)->where('rating', '<=', 2)->count();

        return view('admin.reports.feedbacks', compact(
            'feedbackItems',
            'totalFeedbacks',
            'clinicScore',
            'recommendedCount',
            'lowRatingCount',
            'search',
            'monthFilter'
        ));
    }

    private function buildInventoryReportData(string $monthFilter, string $inventoryScope = 'all')
    {
        $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $consumedByItem = InventoryMovement::query()
            ->select('item_id', DB::raw('SUM(ABS(quantity)) as consumed_total'))
            ->where('type', 'consumed')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->groupBy('item_id')
            ->pluck('consumed_total', 'item_id');

          $itemsQuery = Item::query();

          if ($inventoryScope === 'medicines') {
              $itemsQuery->where('category', 'Medicine');
          } elseif ($inventoryScope === 'supplies') {
              $itemsQuery->where('category', '!=', 'Medicine');
          }

          return $itemsQuery
              ->orderBy('name')
              ->get()
              ->map(function ($item) use ($consumedByItem) {
                  $consumedInStockUnit = (float) ($consumedByItem[$item->id] ?? 0);
                  $item->unit = $item->unit ?: 'pcs';
                  $item->consumed = $consumedInStockUnit;
                  $item->consumed_display = $item->hasDispensingConversion()
                      ? $consumedInStockUnit * $item->unitsPerStockUnit()
                      : $consumedInStockUnit;
                  $item->current_balance = (float) $item->quantity;
                  $item->starting_stock = $item->current_balance + $consumedInStockUnit;
                  $item->report_category = $this->inventoryReportCategoryLabel($item);
                  return $item;
              });
    }

    public function marReport(Request $request)
{
    $monthFilter = $request->input('month', date('Y-m'));
    $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
    $monthEnd = (clone $monthStart)->endOfMonth();
    $dateFromInput = trim((string) $request->input('date_from', ''));
    $dateToInput = trim((string) $request->input('date_to', ''));

    try {
        $dateFrom = $dateFromInput !== '' ? Carbon::parse($dateFromInput)->startOfDay() : (clone $monthStart);
    } catch (\Throwable $exception) {
        $dateFrom = (clone $monthStart);
        $dateFromInput = '';
    }

    try {
        $dateTo = $dateToInput !== '' ? Carbon::parse($dateToInput)->endOfDay() : (clone $monthEnd);
    } catch (\Throwable $exception) {
        $dateTo = (clone $monthEnd);
        $dateToInput = '';
    }

    if ($dateFrom->gt($dateTo)) {
        [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        [$dateFromInput, $dateToInput] = [$dateToInput, $dateFromInput];
    }

    $monthFilter = $dateFrom->format('Y-m');

    // Gamitin ang consultations table columns directly para sa counting
    $categories = Category::with(['medicalConditions.consultations' => function($query) use ($dateFrom, $dateTo) {
        $query->whereBetween('consultation_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
    }])->get();
    $gadTables = $this->buildMarGadTables($categories, $dateFrom, $dateTo);

    $allConditions = MedicalConditions::with('category')->get();
    $categoryList = Category::all();
    $totalToday = Consultation::whereDate('consultation_date', today())->count();

    return view('admin.reports.mar', [
        'categories' => $categories,
        'gadTables' => $gadTables,
        'allConditions' => $allConditions,
        'categoryList' => $categoryList,
        'month' => $monthFilter,
        'dateFrom' => $dateFrom->toDateString(),
        'dateTo' => $dateTo->toDateString(),
        'totalToday' => $totalToday
    ]);
}
    // for managing mar
    public function manageMar(Request $request)
{
    $month = $request->get('month', date('Y-m'));
    
    // Para sa dropdown ng categories
    $categoryList = Category::all(); 
    
    // Para sa listahan ng conditions sa table
    $allConditions = MedicalConditions::with('category')->get(); 

    // Iba pang logic para sa reports...
    $categories = Category::with(['medicalConditions.consultations' => function($query) use ($month) {
        $query->where('consultation_date', 'like', $month . '%');
    }])->get();

    return view('admin.reports.manage-mar', compact('categoryList', 'allConditions', 'categories', 'month'));
}

//for changing category
public function update(Request $request, $id)
{
    $request->validate(['category_id' => 'required|exists:categories,id']);
    
    $condition = MedicalConditions::findOrFail($id);
    $condition->update([
        'category_id' => $request->category_id
    ]);

    return back()->with('success', 'Category updated successfully!');
}
// Para sa Export Hub Landing Page
public function exportHub() 
{
    $healthFormCourses = HealthProfile::query()
        ->with('user:id,course')
        ->get()
        ->map(function (HealthProfile $profile) {
            return trim((string) ($profile->course_college ?: optional($profile->user)->course));
        })
        ->filter()
        ->unique()
        ->sort()
        ->values();

    return view('admin.reports.export-reports', compact('healthFormCourses'));
}

public function exportReportsMar(Request $request)
{
    return $this->exportReportsPreview($request, 'mar');
}

public function exportReportsInventory(Request $request)
{
    return $this->exportReportsPreview($request, 'inventory');
}

public function exportReportsAppointments(Request $request)
{
    return $this->exportReportsPreview($request, 'appointments');
}

public function exportReportsAuditTrail(Request $request)
{
    return $this->exportReportsPreview($request, 'audit-trail');
}

public function exportReportsHealthForms(Request $request)
{
    return $this->exportReportsPreview($request, 'health-forms');
}

private function exportReportsPreview(Request $request, string $reportType)
{
    $role = User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $isAssistant = $role === User::ROLE_ADMIN;
    $reportsHomeUrl = $isAssistant ? url('/assistant/reports') : url('/admin/reports');
    $hubUrl = $isAssistant ? url('/assistant/reports/export-hub') : url('/admin/reports/export-hub');
    $printReportUrl = $isAssistant ? url('/assistant/reports/print-reports') : url('/admin/reports/print-reports');
    $healthFormsExportUrl = $isAssistant ? url('/assistant/reports/health-forms/export') : url('/admin/reports/health-forms/export');

    $dateFrom = $this->exportPreviewDate($request->query('date_from'), now()->startOfMonth());
    $dateTo = $this->exportPreviewDate($request->query('date_to'), now());

    if ($dateFrom->gt($dateTo)) {
        [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
    }

    $monthFilter = $dateFrom->format('Y-m');
    $baseExportQuery = [
        'date_from' => $dateFrom->toDateString(),
        'date_to' => $dateTo->toDateString(),
    ];
    $healthFormsExtraQuery = collect([
        'course' => trim((string) $request->query('course', '')),
        'user_type' => trim((string) $request->query('user_type', '')),
        'gender' => trim((string) $request->query('gender', '')),
        'condition' => trim((string) $request->query('condition', '')),
        'status' => trim((string) $request->query('status', '')),
        'condition_keyword' => trim((string) $request->query('condition_keyword', '')),
        'condition_source' => trim((string) $request->query('condition_source', 'all')),
        'condition_match' => trim((string) $request->query('condition_match', 'any')),
        'course_sheets' => $request->boolean('course_sheets') ? '1' : '',
    ])->filter(fn ($value) => $value !== '')->all();
    $healthFormsBmiCategories = collect((array) $request->query('bmi_categories', []))
        ->flatMap(fn ($value) => explode(',', (string) $value))
        ->map(fn ($value) => strtolower(trim($value)))
        ->filter(fn ($value) => in_array($value, ['underweight', 'normal', 'overweight', 'obese', 'no_bmi'], true))
        ->unique()
        ->values()
        ->all();
    if (!empty($healthFormsBmiCategories)) {
        $healthFormsExtraQuery['bmi_categories'] = $healthFormsBmiCategories;
    }

    $config = [
        'mar' => [
            'view' => 'admin.reports.export-reports-mar',
            'title' => 'MAR Report Export',
            'kicker' => 'Monthly Report',
            'subtitle' => 'Preview medical accomplishment categories before exporting the MAR report.',
            'export_label' => 'MAR Report',
            'export_url' => $printReportUrl . '?' . http_build_query($baseExportQuery + ['type' => 'mar', 'output' => 'pdf', 'month' => $monthFilter]),
        ],
        'inventory' => [
            'view' => 'admin.reports.export-reports-inventory',
            'title' => 'Inventory Stock Export',
            'kicker' => 'Stocks & Supplies',
            'subtitle' => 'Preview inventory balances, consumed quantities, and stock categories before exporting.',
            'export_label' => 'Export Inventory',
            'export_url' => $printReportUrl . '?' . http_build_query($baseExportQuery + ['type' => 'inventory', 'output' => 'pdf', 'month' => $monthFilter, 'inventory_scope' => 'all']),
            'export_links' => [
                [
                    'label' => 'Inventory of Medicines',
                    'url' => $printReportUrl . '?' . http_build_query($baseExportQuery + ['type' => 'inventory', 'output' => 'pdf', 'month' => $monthFilter, 'inventory_scope' => 'medicines']),
                ],
                [
                    'label' => 'Inventory of Supplies',
                    'url' => $printReportUrl . '?' . http_build_query($baseExportQuery + ['type' => 'inventory', 'output' => 'pdf', 'month' => $monthFilter, 'inventory_scope' => 'supplies']),
                ],
            ],
        ],
        'appointments' => [
            'view' => 'admin.reports.export-reports-appointments',
            'title' => 'Appointments Export',
            'kicker' => 'Clinic Activity',
            'subtitle' => 'Preview appointment requests and clinic consultation activity for the selected period.',
            'export_label' => 'Appointments',
            'export_url' => $printReportUrl . '?' . http_build_query($baseExportQuery + ['type' => 'appointment', 'output' => 'pdf', 'month' => $monthFilter]),
        ],
        'audit-trail' => [
            'view' => 'admin.reports.export-reports-audit-trail',
            'title' => 'Audit Trail Export',
            'kicker' => 'System Monitoring',
            'subtitle' => 'Preview system activity logs before exporting the audit trail report.',
            'export_label' => 'Audit Trail',
            'export_url' => $printReportUrl . '?' . http_build_query($baseExportQuery + ['type' => 'audit', 'output' => 'pdf', 'month' => $monthFilter]),
        ],
        'health-forms' => [
            'view' => 'admin.reports.export-reports-health-forms',
            'title' => 'Health Forms Export',
            'kicker' => 'Medical Clearance',
            'subtitle' => 'Preview health form records before exporting the report.',
            'export_label' => 'Health Forms',
            'export_url' => $healthFormsExportUrl . '?' . http_build_query($baseExportQuery + $healthFormsExtraQuery),
        ],
    ][$reportType] ?? null;

    abort_unless($config, 404);

    [$previewHeaders, $previewRows, $previewCount] = $this->exportPreviewTable($reportType, $dateFrom, $dateTo, $request);
    $previewMetrics = $this->exportPreviewMetrics($request, $reportType, $dateFrom, $dateTo, $previewCount);
    $filterActionUrl = $isAssistant
        ? url('/assistant/reports/export-hub/' . $reportType)
        : url('/admin/reports/export-hub/' . $reportType);

    return view($config['view'], [
        'reportType' => $reportType,
        'title' => $config['title'],
        'kicker' => $config['kicker'],
        'subtitle' => $config['subtitle'],
        'exportLabel' => $config['export_label'],
        'exportUrl' => $config['export_url'],
        'exportLinks' => $config['export_links'] ?? [],
        'filterActionUrl' => $filterActionUrl,
        'reportsHomeUrl' => $reportsHomeUrl,
        'hubUrl' => $hubUrl,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'previewHeaders' => $previewHeaders,
        'previewRows' => $previewRows,
        'previewCount' => $previewCount,
        'previewMetrics' => $previewMetrics,
        'healthFormCourses' => HealthProfile::query()
            ->with('user:id,course')
            ->get()
            ->map(fn (HealthProfile $profile) => trim((string) ($profile->course_college ?: optional($profile->user)->course)))
            ->filter()
            ->unique()
            ->sort()
            ->values(),
        'healthConditionSuggestions' => $reportType === 'health-forms'
            ? $this->healthConditionKeywordSuggestions()
            : collect(),
    ]);
}

private function exportPreviewMetrics(Request $request, string $reportType, Carbon $dateFrom, Carbon $dateTo, int $previewCount): array
{
    if ($reportType === 'audit-trail') {
        $latestActivity = ActivityLog::query()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->latest('created_at')
            ->first();

        return [
            'total_activities' => $previewCount,
            'last_audit_export' => optional($latestActivity?->created_at)->format('M d, Y') ?: 'N/A',
        ];
    }

    if ($reportType === 'health-forms') {
        $records = $this->exportHealthFormsPreviewRecords($request, $dateFrom, $dateTo);

        return [
            'total_issued' => $records->count(),
            'with_condition' => $records->filter(fn (HealthProfile $record) => $record->hasMedicalCondition())->count(),
        ];
    }

    if ($reportType !== 'appointments') {
        return [];
    }

    $commonService = Appointment::query()
        ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
        ->get()
        ->map(fn (Appointment $appointment) => trim((string) ($appointment->service ?: 'N/A')))
        ->filter(fn (string $service) => $service !== '')
        ->countBy()
        ->sortDesc()
        ->keys()
        ->first();

    return [
        'total_appointments' => $previewCount,
        'common_service' => $commonService ?: 'N/A',
    ];
}

private function exportPreviewDate($value, Carbon $fallback): Carbon
{
    try {
        $value = trim((string) $value);
        return $value !== '' ? Carbon::parse($value)->startOfDay() : (clone $fallback)->startOfDay();
    } catch (\Throwable $exception) {
        return (clone $fallback)->startOfDay();
    }
}

private function exportPreviewTable(string $reportType, Carbon $dateFrom, Carbon $dateTo, ?Request $request = null): array
{
    if ($reportType === 'mar') {
        $categories = Category::with(['medicalConditions.consultations' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('consultation_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        }])->get();

        $rows = $categories->map(function (Category $category) {
            $consultationCount = $category->medicalConditions->sum(fn ($condition) => $condition->consultations->count());
            return [$category->name, $category->medicalConditions->count(), $consultationCount, 'MAR category'];
        });

        return [['Category', 'Conditions', 'Consultations', 'Preview Type'], $rows->take(10)->values(), $rows->count()];
    }

    if ($reportType === 'inventory') {
        $items = $this->buildInventoryReportData($dateFrom->format('Y-m'), 'all');
        $rows = $items->map(fn ($item) => [
            $item->name,
            $item->report_category,
            $item->unit,
            $this->formatInventoryQuantity((float) $item->starting_stock),
            $this->formatInventoryQuantity((float) $item->consumed),
            $this->formatInventoryQuantity((float) $item->current_balance),
        ]);

        return [['Item', 'Category', 'Unit', 'Starting', 'Consumed', 'Current'], $rows->take(10)->values(), $rows->count()];
    }

    if ($reportType === 'appointments') {
        $appointments = Appointment::query()
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get();
        $rows = $appointments->map(fn (Appointment $appointment) => [
            $appointment->apt_id ?: $appointment->id,
            $appointment->name ?: optional($appointment->user)->name ?: 'N/A',
            $appointment->service ?: 'N/A',
            $appointment->date ? Carbon::parse($appointment->date)->format('M d, Y') : 'N/A',
            $appointment->time ? Carbon::parse($appointment->time)->format('g:i A') : 'N/A',
            ucfirst((string) $appointment->status),
        ]);

        return [['Appointment ID', 'Patient', 'Service', 'Date', 'Time', 'Status'], $rows->take(10)->values(), $rows->count()];
    }

    if ($reportType === 'audit-trail') {
        $logs = ActivityLog::query()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->latest()
            ->get();
        $rows = $logs->map(fn (ActivityLog $log) => [
            optional($log->created_at)->format('M d, Y g:i A') ?: 'N/A',
            $log->user_name ?: 'System',
            $log->action ?: 'N/A',
            Str::limit((string) $log->description, 70),
        ]);

        return [['Date & Time', 'User', 'Action', 'Description'], $rows->take(10)->values(), $rows->count()];
    }

    $request = $request ?? request();
    $records = $this->exportHealthFormsPreviewRecords($request, $dateFrom, $dateTo);
    [$headers, $columns] = $this->exportHealthFormsPreviewColumns($request);

    $rows = $records->map(function (HealthProfile $record) use ($columns) {
        return collect($columns)
            ->map(fn (callable $resolver) => $resolver($record))
            ->values()
            ->all();
    });

    return [$headers, $rows->take(10)->values(), $rows->count()];
}

private function exportHealthFormsPreviewColumns(Request $request): array
{
    $hasCourseFilter = trim((string) $request->query('course', '')) !== '';
    $hasUserTypeFilter = trim((string) $request->query('user_type', '')) !== '';
    $hasGenderFilter = trim((string) $request->query('gender', '')) !== '';
    $hasConditionFilter = trim((string) $request->query('condition', '')) !== '';
    $hasStatusFilter = trim((string) $request->query('status', '')) !== '';
    $hasConditionKeyword = trim((string) $request->query('condition_keyword', '')) !== '';
    $hasBmiFilter = collect((array) $request->query('bmi_categories', []))
        ->flatMap(fn ($value) => explode(',', (string) $value))
        ->filter(fn ($value) => trim((string) $value) !== '')
        ->isNotEmpty();

    $statusLabel = function (HealthProfile $record): string {
        return match (true) {
            in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) => 'Approved',
            $record->clearance_status === 'Rejected' => 'Rejected',
            default => 'Pending',
        };
    };

    $recordDate = function (HealthProfile $record): string {
        $date = $record->verified_at ?: $record->created_at;

        return $date ? Carbon::parse($date)->format('M d, Y g:i A') : 'N/A';
    };

    $definitions = [
        'Reference' => fn (HealthProfile $record) => $record->reference_number ?: $record->student_number ?: optional($record->user)->student_number ?: 'N/A',
        'Name' => fn (HealthProfile $record) => optional($record->user)->name ?: 'N/A',
        'Course' => fn (HealthProfile $record) => $record->course_college ?: optional($record->user)->course ?: 'N/A',
        'User Type' => fn (HealthProfile $record) => optional($record->user)->user_type ?: optional($record->user)->user_role ?: 'N/A',
        'Gender' => fn (HealthProfile $record) => $record->sex ?: optional($record->user)->gender ?: 'N/A',
        'Status' => $statusLabel,
        'With Condition' => fn (HealthProfile $record) => $record->hasMedicalCondition() ? 'Yes' : 'No',
        'Condition Details' => fn (HealthProfile $record) => Str::limit($this->healthProfileConditionText($record), 90) ?: 'N/A',
        'BMI Category' => fn (HealthProfile $record) => $this->healthProfileBmiCategoryLabel($this->healthProfileBmi($record)),
        'BMI' => fn (HealthProfile $record) => ($bmi = $this->healthProfileBmi($record)) !== null ? number_format($bmi, 1) : 'N/A',
        'Record Date' => $recordDate,
    ];

    $headers = ['Reference', 'Name'];

    if (!$hasUserTypeFilter || $hasCourseFilter) {
        $headers[] = 'Course';
    }

    if ($hasUserTypeFilter) {
        $headers[] = 'User Type';
    }

    if ($hasGenderFilter) {
        $headers[] = 'Gender';
    }

    $headers[] = 'Status';

    if (!$hasConditionFilter && !$hasConditionKeyword && !$hasBmiFilter) {
        $headers[] = 'With Condition';
    }

    if ($hasConditionFilter || $hasConditionKeyword) {
        $headers[] = 'With Condition';
        $headers[] = 'Condition Details';
    }

    if ($hasBmiFilter) {
        $headers[] = 'BMI Category';
        $headers[] = 'BMI';
    }

    $headers[] = 'Record Date';
    $headers = collect($headers)->unique()->values();

    return [
        $headers->all(),
        $headers->map(fn (string $header) => $definitions[$header])->all(),
    ];
}

private function exportHealthFormsPreviewRecords(Request $request, Carbon $dateFrom, Carbon $dateTo): Collection
{
    $courseFilter = trim((string) $request->query('course', ''));
    $userTypeFilter = strtolower(trim((string) $request->query('user_type', '')));
    $genderFilter = strtolower(trim((string) $request->query('gender', '')));
    $conditionFilter = strtolower(trim((string) $request->query('condition', '')));
    $statusFilter = strtolower(trim((string) $request->query('status', '')));
    $conditionKeyword = trim((string) $request->query('condition_keyword', ''));
    $conditionSource = trim((string) $request->query('condition_source', 'all'));
    $conditionMatch = trim((string) $request->query('condition_match', 'any'));
    $bmiCategories = collect((array) $request->query('bmi_categories', []))
        ->flatMap(fn ($value) => explode(',', (string) $value))
        ->map(fn ($value) => strtolower(trim($value)))
        ->filter(fn ($value) => in_array($value, ['underweight', 'normal', 'overweight', 'obese', 'no_bmi'], true))
        ->unique()
        ->values();

    if (!in_array($conditionSource, ['all', 'health_form', 'final_review', 'remarks'], true)) {
        $conditionSource = 'all';
    }

    if (!in_array($conditionMatch, ['any', 'all', 'exact'], true)) {
        $conditionMatch = 'any';
    }

    $query = HealthProfile::query()
        ->with('user')
        ->whereNotNull('clearance_status')
        ->where(function ($builder) use ($dateFrom, $dateTo) {
            $builder->whereBetween('verified_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
                ->orWhere(function ($fallback) use ($dateFrom, $dateTo) {
                    $fallback->whereNull('verified_at')
                        ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);
                });
        });

    if ($courseFilter !== '') {
        $query->where(function ($builder) use ($courseFilter) {
            $builder->where('course_college', 'like', "%{$courseFilter}%")
                ->orWhereHas('user', function ($userQuery) use ($courseFilter) {
                    $userQuery->where('course', 'like', "%{$courseFilter}%");
                });
        });
    }

    if ($statusFilter === 'approved') {
        $query->whereIn('clearance_status', ['Issued', 'Fully Cleared']);
    } elseif ($statusFilter === 'pending') {
        $query->whereNotIn('clearance_status', ['Issued', 'Fully Cleared', 'Rejected']);
    } elseif ($statusFilter === 'rejected') {
        $query->where('clearance_status', 'Rejected');
    }

    $records = $query->orderByDesc(DB::raw('COALESCE(verified_at, created_at)'))->get();

    if ($userTypeFilter !== '') {
        $records = $records->filter(function (HealthProfile $record) use ($userTypeFilter) {
            $user = $record->user;
            $role = strtolower(trim((string) (optional($user)->user_type ?: optional($user)->user_role)));
            return $role === $userTypeFilter || str_contains($role, $userTypeFilter);
        })->values();
    }

    if ($genderFilter !== '') {
        $records = $records->filter(function (HealthProfile $record) use ($genderFilter) {
            $gender = strtolower(trim((string) ($record->sex ?: optional($record->user)->gender)));
            return $gender === $genderFilter || str_contains($gender, $genderFilter);
        })->values();
    }

    if ($conditionFilter === 'yes') {
        $records = $records->filter(fn (HealthProfile $record) => $record->hasMedicalCondition())->values();
    } elseif ($conditionFilter === 'no') {
        $records = $records->filter(fn (HealthProfile $record) => ! $record->hasMedicalCondition())->values();
    }

    if ($bmiCategories->isNotEmpty()) {
        $records = $records->filter(function (HealthProfile $record) use ($bmiCategories) {
            $bmi = $this->healthProfileBmi($record);
            return $bmiCategories->contains($this->healthProfileBmiCategory($bmi));
        })->values();
    }

    if ($conditionKeyword !== '') {
        $terms = collect(preg_split('/[\s,]+/', mb_strtolower($conditionKeyword), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($term) => trim($term))
            ->filter()
            ->values();
        $exactNeedle = mb_strtolower($conditionKeyword);

        $records = $records->filter(function (HealthProfile $record) use ($conditionSource, $conditionMatch, $terms, $exactNeedle) {
            $haystack = mb_strtolower($this->healthProfileConditionText($record, $conditionSource));

            if ($conditionMatch === 'exact') {
                return $haystack === $exactNeedle || str_contains($haystack, $exactNeedle);
            }

            if ($terms->isEmpty()) {
                return true;
            }

            if ($conditionMatch === 'all') {
                return $terms->every(fn ($term) => str_contains($haystack, $term));
            }

            return $terms->contains(fn ($term) => str_contains($haystack, $term));
        })->values();
    }

    return $records->values();
}

public function exportHealthForms(Request $request)
{
    $dateFromInput = trim((string) $request->query('date_from', now()->startOfMonth()->toDateString()));
    $dateToInput = trim((string) $request->query('date_to', now()->toDateString()));
    $courseFilter = trim((string) $request->query('course', ''));
    $userTypeFilter = strtolower(trim((string) $request->query('user_type', '')));
    $genderFilter = strtolower(trim((string) $request->query('gender', '')));
    $conditionFilter = strtolower(trim((string) $request->query('condition', '')));
    $statusFilter = strtolower(trim((string) $request->query('status', '')));
    $conditionKeyword = trim((string) $request->query('condition_keyword', ''));
    $conditionSource = trim((string) $request->query('condition_source', 'all'));
    $conditionMatch = trim((string) $request->query('condition_match', 'any'));
    $bmiCategories = collect((array) $request->query('bmi_categories', []))
        ->flatMap(fn ($value) => explode(',', (string) $value))
        ->map(fn ($value) => strtolower(trim($value)))
        ->filter(fn ($value) => in_array($value, ['underweight', 'normal', 'overweight', 'obese', 'no_bmi'], true))
        ->unique()
        ->values();

    if (!in_array($conditionSource, ['all', 'health_form', 'final_review', 'remarks'], true)) {
        $conditionSource = 'all';
    }

    if (!in_array($conditionMatch, ['any', 'all', 'exact'], true)) {
        $conditionMatch = 'any';
    }

    $dateFrom = $dateFromInput !== ''
        ? Carbon::parse($dateFromInput)->startOfDay()
        : now()->startOfMonth();
    $dateTo = $dateToInput !== ''
        ? Carbon::parse($dateToInput)->endOfDay()
        : now()->endOfDay();

    if ($dateFrom->gt($dateTo)) {
        [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
    }

    $query = HealthProfile::query()
        ->with('user')
        ->whereNotNull('clearance_status')
        ->where(function ($builder) use ($dateFrom, $dateTo) {
            $builder->whereBetween('verified_at', [$dateFrom, $dateTo])
                ->orWhere(function ($fallback) use ($dateFrom, $dateTo) {
                    $fallback->whereNull('verified_at')
                        ->whereBetween('created_at', [$dateFrom, $dateTo]);
                });
        });

    if ($courseFilter !== '') {
        $query->where(function ($builder) use ($courseFilter) {
            $builder->where('course_college', 'like', "%{$courseFilter}%")
                ->orWhereHas('user', function ($userQuery) use ($courseFilter) {
                    $userQuery->where('course', 'like', "%{$courseFilter}%");
                });
        });
    }

    if ($statusFilter === 'approved') {
        $query->whereIn('clearance_status', ['Issued', 'Fully Cleared']);
    } elseif ($statusFilter === 'pending') {
        $query->whereNotIn('clearance_status', ['Issued', 'Fully Cleared', 'Rejected']);
    } elseif ($statusFilter === 'rejected') {
        $query->where('clearance_status', 'Rejected');
    }

    $records = $query->orderByDesc(DB::raw('COALESCE(verified_at, created_at)'))->get();

    if ($userTypeFilter !== '') {
        $records = $records->filter(function (HealthProfile $record) use ($userTypeFilter) {
            $user = $record->user;
            $role = strtolower(trim((string) (optional($user)->user_type ?: optional($user)->user_role)));
            return $role === $userTypeFilter || str_contains($role, $userTypeFilter);
        })->values();
    }

    if ($genderFilter !== '') {
        $records = $records->filter(function (HealthProfile $record) use ($genderFilter) {
            $gender = strtolower(trim((string) ($record->sex ?: optional($record->user)->gender)));
            return $gender === $genderFilter || str_contains($gender, $genderFilter);
        })->values();
    }

    if ($conditionFilter === 'yes') {
        $records = $records->filter(fn (HealthProfile $record) => $record->hasMedicalCondition())->values();
    } elseif ($conditionFilter === 'no') {
        $records = $records->filter(fn (HealthProfile $record) => ! $record->hasMedicalCondition())->values();
    }

    if ($bmiCategories->isNotEmpty()) {
        $records = $records->filter(function (HealthProfile $record) use ($bmiCategories) {
            $bmi = $this->healthProfileBmi($record);
            return $bmiCategories->contains($this->healthProfileBmiCategory($bmi));
        })->values();
    }

    if ($conditionKeyword !== '') {
        $terms = collect(preg_split('/[\s,]+/', mb_strtolower($conditionKeyword), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($term) => trim($term))
            ->filter()
            ->values();
        $exactNeedle = mb_strtolower($conditionKeyword);

        $records = $records->filter(function (HealthProfile $record) use ($conditionSource, $conditionMatch, $terms, $exactNeedle) {
            $haystack = mb_strtolower($this->healthProfileConditionText($record, $conditionSource));

            if ($conditionMatch === 'exact') {
                return $haystack === $exactNeedle || str_contains($haystack, $exactNeedle);
            }

            if ($terms->isEmpty()) {
                return true;
            }

            if ($conditionMatch === 'all') {
                return $terms->every(fn ($term) => str_contains($haystack, $term));
            }

            return $terms->contains(fn ($term) => str_contains($haystack, $term));
        })->values();
    }

    if ($request->boolean('course_sheets')) {
        return $this->exportHealthFormsCourseSheets($records, $dateFrom, $dateTo);
    }

    $filename = 'health-forms-' . $dateFrom->format('Ymd') . '-' . $dateTo->format('Ymd') . '-' . now()->format('His') . '.csv';

    return response()->streamDownload(function () use ($records) {
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Reference Number',
            'Full Name',
            'Course',
            'Gender',
            'Status',
            'Medical Condition',
            'BMI',
            'BMI Category',
            'Condition Details',
            'Approval Date and Time',
        ]);

        foreach ($records as $record) {
            $status = match (true) {
                in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) => 'Approved',
                $record->clearance_status === 'Rejected' => 'Rejected',
                default => 'Pending',
            };

            fputcsv($output, [
                $record->reference_number ?: $record->student_number ?: optional($record->user)->student_number ?: 'N/A',
                optional($record->user)->name ?: 'N/A',
                $record->course_college ?: optional($record->user)->course ?: 'N/A',
                $record->sex ?: optional($record->user)->gender ?: 'N/A',
                $status,
                $record->hasMedicalCondition() ? 'Yes' : 'No',
                ($bmi = $this->healthProfileBmi($record)) !== null ? number_format($bmi, 1) : 'N/A',
                $this->healthProfileBmiCategoryLabel($bmi ?? null),
                $this->healthProfileConditionText($record, 'all') ?: 'N/A',
                $record->verified_at ? Carbon::parse($record->verified_at)->format('M d, Y g:i A') : 'N/A',
            ]);
        }

        fclose($output);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
    ]);
}

private function exportHealthFormsCourseSheets(Collection $records, Carbon $dateFrom, Carbon $dateTo)
{
    $applicantRecords = $records
        ->filter(fn (HealthProfile $record) => $this->healthFormsCourseSheetIsApplicant($record))
        ->values();

    $sheets = $applicantRecords
        ->groupBy(fn (HealthProfile $record) => $this->healthFormsCourseSheetCourse($record))
        ->sortKeys()
        ->map(function (Collection $courseRecords) {
            return $courseRecords
                ->sortBy(function (HealthProfile $record) {
                    $statusRank = $this->healthFormsCourseSheetStatusRank($record);
                    $date = $statusRank === 0
                        ? ($record->verified_at ?: $record->created_at)
                        : ($record->created_at ?: $record->updated_at);

                    return sprintf(
                        '%d-%s-%010d',
                        $statusRank,
                        $date ? Carbon::parse($date)->format('YmdHis') : '99999999999999',
                        $record->id
                    );
                })
                ->values();
        });

    if ($sheets->isEmpty()) {
        $sheets = collect(['No Applicants' => collect()]);
    }

    $headers = [
        'Reference Number',
        'Full Name',
        'Course',
        'Gender',
        'Status',
        'Medical Condition',
        'BMI',
        'BMI Category',
        'Condition Details',
        'Approval Date and Time',
    ];

    $workbookXml = $this->healthFormsCourseSheetsWorkbookXml($sheets, $headers);
    $filename = 'health-forms-course-sheets-' . $dateFrom->format('Ymd') . '-' . $dateTo->format('Ymd') . '-' . now()->format('His') . '.xls';

    return response($workbookXml, 200, [
        'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
    ]);
}

private function healthFormsCourseSheetsWorkbookXml(Collection $sheets, array $headers): string
{
    $usedSheetNames = [];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
    $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
        . 'xmlns:o="urn:schemas-microsoft-com:office:office" '
        . 'xmlns:x="urn:schemas-microsoft-com:office:excel" '
        . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" '
        . 'xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
    $xml .= '<Styles><Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#F8E8EA" ss:Pattern="Solid"/></Style></Styles>' . "\n";

    foreach ($sheets as $course => $records) {
        $sheetName = $this->healthFormsCourseSheetName((string) $course, $usedSheetNames);
        $xml .= '<Worksheet ss:Name="' . $this->excelXml($sheetName) . '"><Table>' . "\n";
        $xml .= '<Row>';
        foreach ($headers as $header) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . $this->excelXml($header) . '</Data></Cell>';
        }
        $xml .= '</Row>' . "\n";

        foreach ($records as $record) {
            $xml .= '<Row>';
            foreach ($this->healthFormsCourseSheetRow($record) as $value) {
                $xml .= '<Cell><Data ss:Type="String">' . $this->excelXml($value) . '</Data></Cell>';
            }
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table></Worksheet>' . "\n";
    }

    $xml .= '</Workbook>';

    return $xml;
}

private function healthFormsCourseSheetRow(HealthProfile $record): array
{
    $bmi = $this->healthProfileBmi($record);

    return [
        $record->reference_number ?: $record->student_number ?: optional($record->user)->student_number ?: 'N/A',
        optional($record->user)->name ?: 'N/A',
        $this->healthFormsCourseSheetCourse($record),
        $record->sex ?: optional($record->user)->gender ?: 'N/A',
        $this->healthFormsCourseSheetStatus($record),
        $record->hasMedicalCondition() ? 'Yes' : 'No',
        $bmi !== null ? number_format($bmi, 1) : 'N/A',
        $this->healthProfileBmiCategoryLabel($bmi),
        $this->healthProfileConditionText($record, 'all') ?: 'N/A',
        $record->verified_at ? Carbon::parse($record->verified_at)->format('M d, Y g:i A') : 'N/A',
    ];
}

private function healthFormsCourseSheetIsApplicant(HealthProfile $record): bool
{
    $role = strtolower(trim((string) (optional($record->user)->user_type ?: optional($record->user)->user_role)));

    return $role === 'applicant' || str_contains($role, 'applicant');
}

private function healthFormsCourseSheetCourse(HealthProfile $record): string
{
    $course = trim((string) ($record->course_college ?: optional($record->user)->course ?: ''));

    return $course !== '' ? $course : 'No Course';
}

private function healthFormsCourseSheetStatus(HealthProfile $record): string
{
    return match (true) {
        in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) => 'Approved',
        $record->clearance_status === 'Rejected' => 'Rejected',
        default => 'Pending',
    };
}

private function healthFormsCourseSheetStatusRank(HealthProfile $record): int
{
    return match ($this->healthFormsCourseSheetStatus($record)) {
        'Approved' => 0,
        'Rejected' => 1,
        default => 2,
    };
}

private function healthFormsCourseSheetName(string $course, array &$usedSheetNames): string
{
    $name = trim(preg_replace('/[:\\\\\/\?\*\[\]]+/', ' ', $course) ?? '');
    $name = $name !== '' ? $name : 'No Course';
    $name = mb_substr($name, 0, 31);
    $base = $name;
    $counter = 2;

    while (in_array(mb_strtolower($name), $usedSheetNames, true)) {
        $suffix = ' ' . $counter++;
        $name = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
    }

    $usedSheetNames[] = mb_strtolower($name);

    return $name;
}

private function excelXml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

// Para sa Universal Printing System
public function printReport(Request $request)
{
    $type = $request->query('type'); // mar, inventory, or appointment
    $output = trim((string) $request->query('output', 'html'));
    $inventoryScope = trim((string) $request->query('inventory_scope', 'all'));
    if (!in_array($inventoryScope, ['all', 'medicines', 'supplies'], true)) {
        $inventoryScope = 'all';
    }
    $monthFilter = $request->input('month', date('Y-m'));
    $year = date('Y', strtotime($monthFilter));
    $month = date('m', strtotime($monthFilter));
    $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
    $monthEnd = (clone $monthStart)->endOfMonth();
    $dateFromInput = trim((string) $request->input('date_from', ''));
    $dateToInput = trim((string) $request->input('date_to', ''));
    $dateFrom = $dateFromInput !== '' ? Carbon::parse($dateFromInput)->startOfDay() : (clone $monthStart);
    $dateTo = $dateToInput !== '' ? Carbon::parse($dateToInput)->endOfDay() : (clone $monthEnd);

    if ($dateFrom->gt($dateTo)) {
        [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
    }

    $title = "";
    $data = [];

    if ($type == 'mar') {
        $title = "MONTHLY ACCOMPLISHMENT REPORT";
        // for categories
        $data = \App\Models\Category::with(['medicalConditions.consultations' => function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('consultation_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);
        }])->get();
        $gadTables = $this->buildMarGadTables($data, $dateFrom, $dateTo);
    } 
    elseif ($type == 'inventory') {
        $title = match ($inventoryScope) {
            'medicines' => "INVENTORY OF MEDICINES",
            'supplies' => "INVENTORY OF SUPPLIES",
            default => "INVENTORY STOCK REPORT",
        };
        $data = $this->buildInventoryReportData($monthFilter, $inventoryScope);
        $inventoryReportAsOf = $monthEnd->isCurrentMonth()
            ? now()->endOfDay()
            : (clone $monthEnd);
        $inventoryPreparedBy = auth('admin')->user() ?? auth()->user();

        if ($inventoryPreparedBy) {
            $inventoryPreparedBy->loadMissing('adminProfile');
        }
    }
    elseif ($type == 'appointment') {
    $title = "APPOINTMENT SUMMARY REPORT";
    // for date
    $data = \App\Models\Appointment::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get();
    }
    elseif ($type == 'audit') {
        $title = "AUDIT TRAIL REPORT";
        $data = ActivityLog::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->latest()
            ->get();
    }
    elseif ($type == 'health_forms') {
        $title = "ISSUED HEALTH FORMS REPORT";
        $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $data = HealthProfile::query()
            ->with('user')
            ->where('clearance_status', 'Issued')
            ->where(function ($builder) use ($monthStart, $monthEnd) {
                $builder->whereBetween('verified_at', [$monthStart, $monthEnd])
                    ->orWhere(function ($fallback) use ($monthStart, $monthEnd) {
                        $fallback->whereNull('verified_at')
                            ->whereBetween('created_at', [$monthStart, $monthEnd]);
                    });
            })
            ->get()
            ->groupBy(function (HealthProfile $form) {
                $course = trim((string) ($form->course_college ?: optional($form->user)->course ?: 'Unspecified Course'));
                return $course !== '' ? $course : 'Unspecified Course';
            })
            ->map(function ($forms, $course) {
                $sortedForms = $forms->sortByDesc(function (HealthProfile $form) {
                    return $form->verified_at ?? $form->created_at;
                })->values();

                $withConditionCount = $forms->filter(fn (HealthProfile $form) => $form->hasMedicalCondition())->count();
                $issuedCount = $forms->count();

                return (object) [
                    'course' => $course,
                    'issued_count' => $issuedCount,
                    'with_condition_count' => $withConditionCount,
                    'no_condition_count' => $issuedCount - $withConditionCount,
                    'last_issued_at' => optional($sortedForms->first())->verified_at ?? optional($sortedForms->first())->created_at,
                ];
            })
            ->sortByDesc('issued_count')
            ->values();
    }

    if ($output === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
        $pdf = Pdf::loadView('admin.reports.print-reports', [
            'data' => $data,
            'type' => $type,
            'title' => $title,
            'monthFilter' => $monthFilter,
            'inventoryScope' => $inventoryScope,
            'inventoryReportAsOf' => $inventoryReportAsOf ?? null,
            'inventoryPreparedBy' => $inventoryPreparedBy ?? null,
            'gadTables' => $gadTables ?? [],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'isPdf' => true,
        ])->setPaper('a4', 'portrait');

        $fileType = $type === 'inventory' && $inventoryScope !== 'all' ? 'inventory-' . $inventoryScope : ($type !== '' ? $type : 'report');
        $safeMonth = preg_replace('/[^0-9\-]/', '', $monthFilter) ?: now()->format('Y-m');

        return $pdf->stream(
            "{$fileType}-report-{$safeMonth}-" . now()->format('YmdHis') . '.pdf',
            [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    return response()->view('admin.reports.print-reports', [
        'data' => $data,
        'type' => $type,
        'title' => $title,
        'monthFilter' => $monthFilter,
        'inventoryScope' => $inventoryScope,
        'inventoryReportAsOf' => $inventoryReportAsOf ?? null,
        'inventoryPreparedBy' => $inventoryPreparedBy ?? null,
        'gadTables' => $gadTables ?? [],
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'isPdf' => false,
        'pdfUnavailable' => $output === 'pdf',
    ])->withHeaders([
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}
}
