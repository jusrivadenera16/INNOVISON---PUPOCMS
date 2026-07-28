<?php

namespace App\Console\Commands;

use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillHealthFormSubmissions extends Command
{
    protected $signature = 'health-forms:backfill-submissions {--dry-run : Show what would be generated without saving files}';

    protected $description = 'Generate saved PDF snapshots for existing health profiles that do not have one yet.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $query = HealthProfile::query()
            ->with('user')
            ->whereDoesntHave('healthFormSubmissions', function ($query) {
                $query->whereNotNull('pdf_path');
            })
            ->whereNotNull('user_id');

        $count = (clone $query)->count();
        $this->info(($dryRun ? 'Dry run: ' : '') . "Found {$count} health profile(s) without saved Health Form PDFs.");

        $generated = 0;
        $query->orderBy('id')->chunkById(50, function ($profiles) use ($dryRun, &$generated) {
            foreach ($profiles as $profile) {
                if (!$profile->user) {
                    continue;
                }

                $identifier = trim((string) (
                    $profile->reference_number
                    ?: $profile->user->reference_number
                    ?: $profile->student_number
                    ?: $profile->user->student_number
                    ?: $profile->user->student_id
                    ?: $profile->id
                ));
                $safeIdentifier = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $identifier) ?: (string) $profile->id;
                $timestamp = now();
                $filePath = 'health_form_submissions/' . $profile->user_id . '/health-form-' . $safeIdentifier . '-backfill-' . $timestamp->format('Ymd-His') . '.pdf';

                $this->line(($dryRun ? '[dry-run] ' : '') . "Generating {$filePath}");
                if ($dryRun) {
                    $generated++;
                    continue;
                }

                $pdf = Pdf::loadView('student.print_health_form', [
                    'profile' => $profile,
                    'pdfMode' => true,
                    'healthFormSubmittedAt' => $profile->created_at ?: $timestamp,
                ]);
                $pdf->setPaper([0, 0, 612, 936]);
                Storage::disk('public')->put($filePath, $pdf->output());

                HealthFormSubmission::create([
                    'user_id' => $profile->user_id,
                    'health_profile_id' => $profile->id,
                    'category' => 'Backfilled Record',
                    'school_year' => trim((string) $profile->school_year) ?: null,
                    'status' => HealthFormSubmission::STATUS_SUBMITTED,
                    'pdf_path' => $filePath,
                    'submitted_at' => $profile->created_at ?: $timestamp,
                    'remarks' => 'Generated from existing Health Profile record.',
                ]);

                $generated++;
            }
        });

        $this->info(($dryRun ? 'Would generate' : 'Generated') . " {$generated} Health Form PDF snapshot(s).");

        return self::SUCCESS;
    }
}
