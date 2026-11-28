<?php

namespace App\Console\Commands;

use App\Models\HealthPdfExport;
use App\Services\HealthFormsPdfExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CleanupHealthPdfExports extends Command
{
    protected $signature = 'health-pdf:cleanup';
    protected $description = 'Remove expired temporary Health Forms PDF exports, never source health records.';

    public function handle(HealthFormsPdfExport $builder): int
    {
        if (!Schema::hasTable('health_pdf_exports')) return self::SUCCESS;
        $count = 0;
        HealthPdfExport::where('expires_at', '<=', now())->chunkById(100, function ($exports) use ($builder, &$count) {
            foreach ($exports as $export) {
                $lock = Cache::lock('health-pdf:'.$export->id, 150);
                if (!$lock->get()) continue;
                try {
                    $builder->discardFiles($export);
                    $export->delete();
                    $count++;
                } finally { $lock->release(); }
            }
        });
        $this->info("Removed {$count} expired temporary PDF export(s).");
        return self::SUCCESS;
    }
}
