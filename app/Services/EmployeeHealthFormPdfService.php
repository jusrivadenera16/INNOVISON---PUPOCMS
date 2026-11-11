<?php

namespace App\Services;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeHealthFormPdfService
{
    public function __construct(private HealthFileStorage $healthFiles)
    {
    }

    public function generate(EmployeeHealthProfile $profile): string
    {
        return DB::transaction(function () use ($profile) {
            EmployeeHealthProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            return $this->writePdf($profile);
        });
    }

    private function writePdf(EmployeeHealthProfile $profile): string
    {
        $profile->refresh();
        $profile->loadMissing('user');
        $renderProfile = $profile;
        $approvedToRebuild = null;

        if (in_array(strtolower(trim((string) $profile->clearance_status)), ['issued', 'fully cleared', 'approved', 'cleared'], true)
            && Schema::hasColumn('health_form_submissions', 'employee_health_profile_id')) {
            $submissions = HealthFormSubmission::query()->where('employee_health_profile_id', $profile->id);
            $approved = (clone $submissions)->where('status', HealthFormSubmission::STATUS_APPROVED)->latest('approved_at')->latest('id')->first();
            $hasPending = (clone $submissions)->whereNull('approved_at')
                ->when($approved, fn ($query) => $query->where('id', '>', $approved->id))
                ->whereIn('status', [HealthFormSubmission::STATUS_SUBMITTED, HealthFormSubmission::STATUS_NEEDS_CORRECTION, 'under_review'])->exists();
            if (!$hasPending && $approved && $profile->verified_at && $approved->approved_at
                && $profile->verified_at->equalTo($approved->approved_at)) {
                if (filled($approved->pdf_path) && $this->healthFiles->exists($approved->pdf_path)) {
                    return $approved->pdf_path;
                }
                if ($approved->snapshotProfile() === []) {
                    throw new \RuntimeException('The approved Employee Health Form is missing its frozen snapshot.');
                }
                $approvedToRebuild = $approved;
                $renderProfile = clone $profile;
                $renderProfile->forceFill($approved->snapshotProfile());
                if ($profile->user) {
                    $frozenUser = clone $profile->user;
                    $frozenUser->setRawAttributes(array_merge($frozenUser->getAttributes(), $approved->snapshotUser()), true);
                    $renderProfile->setRelation('user', $frozenUser);
                }
            }
        }

        $identifier = trim((string) (
            $profile->employee_number
            ?: $profile->user?->employee_number
            ?: $profile->id
        ));
        $safeIdentifier = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $identifier)
            ?: 'employee-' . $profile->id;
        $path = 'health_profile_employees/health_forms/health-form-'
            . $safeIdentifier . '-' . now()->format('YmdHis') . '-' . Str::uuid() . '.pdf';

        $previousPath = $this->normalizeStoragePath((string) $profile->staff_health_form_pdf_path);

        $pdf = Pdf::loadView('student.print_employee_health_form', [
            'user' => $renderProfile->user,
            'employeeProfile' => $renderProfile,
            'adminViewer' => true,
            'pdfMode' => true,
        ])->setPaper([0, 0, 612, 936]);

        if (!$this->healthFiles->put($path, $pdf->output())) {
            throw new \RuntimeException('Unable to write the Employee Health Form PDF.');
        }

        try {
            $profile->staff_health_form_pdf_path = $path;
            $profile->save();
            if ($approvedToRebuild) {
                $approvedToRebuild->forceFill(['pdf_path' => $path])->save();
            }
        } catch (\Throwable $exception) {
            if ($path !== $previousPath) {
                $this->healthFiles->delete($path);
            }

            throw $exception;
        }

        if (
            $previousPath !== ''
            && $previousPath !== $path
            && str_starts_with($previousPath, 'health_profile_employees/health_forms/')
            && $this->healthFiles->exists($previousPath)
            && !HealthFormSubmission::query()
                ->where('employee_health_profile_id', $profile->id)
                ->where('pdf_path', $previousPath)
                ->exists()
        ) {
            $this->healthFiles->delete($previousPath);
        }

        return $path;
    }

    private function normalizeStoragePath(string $path): string
    {
        $path = ltrim($path, '/');

        return preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;
    }
}
