<?php

namespace App\Console\Commands;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Services\EmployeeHealthFormPdfService;
use App\Services\HealthFormPdfSnapshotService;
use Illuminate\Console\Command;

class RefreshHealthFormPdfs extends Command
{
    protected $signature = 'health-forms:refresh-pdfs
        {--type=all : Records to refresh: all, students, or employees}
        {--dry-run : List affected records without replacing any PDFs}';

    protected $description = 'Rebuild saved Health Form PDFs so stored e-signatures use the current PDF template.';

    public function handle(
        HealthFormPdfSnapshotService $studentPdfs,
        EmployeeHealthFormPdfService $employeePdfs
    ): int {
        $type = strtolower(trim((string) $this->option('type')));
        if (!in_array($type, ['all', 'student', 'students', 'applicant', 'applicants', 'employee', 'employees'], true)) {
            $this->error('Invalid --type. Use all, students, or employees.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $refreshStudents = in_array($type, ['all', 'student', 'students', 'applicant', 'applicants'], true);
        $refreshEmployees = in_array($type, ['all', 'employee', 'employees'], true);
        $refreshed = 0;
        $failed = 0;

        if ($refreshStudents) {
            $query = HealthProfile::query()
                ->with('user')
                ->whereNotNull('digital_signature')
                ->where('digital_signature', '!=', '')
                ->whereHas('healthFormSubmissions', function ($query) {
                    $query->whereNotNull('pdf_path')
                        ->whereIn('status', [
                            HealthFormSubmission::STATUS_SUBMITTED,
                            HealthFormSubmission::STATUS_APPROVED,
                            HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                        ]);
                });

            $this->info(($dryRun ? 'Dry run: ' : '') . $query->count() . ' signed student/applicant PDF(s) found.');

            $query->orderBy('id')->chunkById(50, function ($profiles) use (
                $studentPdfs,
                $dryRun,
                &$refreshed,
                &$failed
            ) {
                foreach ($profiles as $profile) {
                    $label = 'student/applicant profile #' . $profile->id;
                    if ($dryRun) {
                        $this->line('[dry-run] Would refresh ' . $label);
                        $refreshed++;
                        continue;
                    }

                    try {
                        $submission = $studentPdfs->refreshExistingSnapshot($profile);
                        if (!$submission) {
                            $this->warn('Skipped ' . $label . ': no active saved PDF was found.');
                            continue;
                        }

                        $this->line('Refreshed ' . $label . ' -> ' . $submission->pdf_path);
                        $refreshed++;
                    } catch (\Throwable $exception) {
                        $this->error('Failed ' . $label . ': ' . $exception->getMessage());
                        $failed++;
                    }
                }
            });
        }

        if ($refreshEmployees) {
            $query = EmployeeHealthProfile::query()
                ->with('user')
                ->whereNotNull('staff_health_form_pdf_path')
                ->where('staff_health_form_pdf_path', '!=', '')
                ->where(function ($query) {
                    $query->whereNotNull('uploaded_signature_path')
                        ->where('uploaded_signature_path', '!=', '')
                        ->orWhere(function ($query) {
                            $query->whereNotNull('staff_signature')
                                ->where('staff_signature', '!=', '');
                        });
                });

            $this->info(($dryRun ? 'Dry run: ' : '') . $query->count() . ' signed employee PDF(s) found.');

            $query->orderBy('id')->chunkById(50, function ($profiles) use (
                $employeePdfs,
                $dryRun,
                &$refreshed,
                &$failed
            ) {
                foreach ($profiles as $profile) {
                    $label = 'employee profile #' . $profile->id;
                    if ($dryRun) {
                        $this->line('[dry-run] Would refresh ' . $label);
                        $refreshed++;
                        continue;
                    }

                    try {
                        $path = $employeePdfs->generate($profile);
                        $this->line('Refreshed ' . $label . ' -> ' . $path);
                        $refreshed++;
                    } catch (\Throwable $exception) {
                        $this->error('Failed ' . $label . ': ' . $exception->getMessage());
                        $failed++;
                    }
                }
            });
        }

        $this->info(($dryRun ? 'Would refresh ' : 'Refreshed ') . $refreshed . ' PDF(s).');
        if ($failed > 0) {
            $this->error($failed . ' PDF(s) could not be refreshed.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
