<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupConsultationDrafts extends Command
{
    protected $signature = 'consultation-drafts:cleanup';

    protected $description = 'Delete consultation drafts older than the clinic-approved retention period.';

    public function handle(): int
    {
        $retentionDays = (int) config('consultation_drafts.retention_days', 0);
        if ($retentionDays < 1) {
            $this->info('Cleanup is disabled until a consultation-draft retention period is configured.');

            return self::SUCCESS;
        }

        if (!Schema::hasTable('consultation_drafts')) {
            $this->info('Consultation drafts table does not exist; nothing to clean up.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($retentionDays);
        $deleted = DB::table('consultation_drafts')
            ->where(function ($query) use ($cutoff): void {
                $query->where('updated_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff): void {
                        $query->whereNull('updated_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->delete();

        $this->info("Deleted {$deleted} consultation draft(s) older than {$retentionDays} day(s).");

        return self::SUCCESS;
    }
}
