<?php

namespace App\Jobs;

use App\Models\HealthPdfExport;
use App\Services\HealthFormsPdfExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuildHealthFormsPdf implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // Lock-contention releases are not failures; actual exceptions still have a strict retry budget.
    public $tries = 0;
    public $maxExceptions = 3;
    public $timeout = 120;
    public $failOnTimeout = true;

    public function __construct(public string $exportId)
    {
        // A stale config cache must never fall back to the application's synchronous queue.
        $this->onConnection('health_pdf');
        $this->onQueue('health-pdf');
    }

    public function handle(HealthFormsPdfExport $builder): void
    {
        $lock = Cache::lock('health-pdf:'.$this->exportId, 150);
        if (!$lock->get()) {
            $this->release(5);
            return;
        }
        try {
            $export = HealthPdfExport::find($this->exportId);
            if (!$export || $export->terminal() || $export->expires_at->isPast()) return;
            $builder->advance($export);
            if (!$export->fresh()->terminal()) self::dispatch($this->exportId);
        } catch (Throwable $exception) {
            Log::warning('Health forms PDF batch could not complete', ['export_id' => $this->exportId, 'exception_type' => get_class($exception),
                'location' => basename($exception->getFile()).':'.$exception->getLine()]);
            // failed_jobs must not receive SQL bindings, patient content or template snippets.
            throw new \RuntimeException('The Health Forms PDF background batch could not complete.');
        } finally {
            $lock->release();
            gc_collect_cycles();
        }
    }

    public function failed(?Throwable $exception): void
    {
        $export = HealthPdfExport::find($this->exportId);
        if ($export && !$export->terminal()) {
            $export->update(['status' => 'failed', 'filters' => [], 'context' => []]);
            try { app(HealthFormsPdfExport::class)->discardFiles($export); }
            catch (Throwable $cleanupError) {
                Log::warning('PDF temporary-file cleanup will be retried at expiry', ['export_id' => $this->exportId, 'exception_type' => get_class($cleanupError)]);
            }
        }
        // No names, filters, documents or exception messages in the log.
        Log::warning('Health forms PDF export failed', ['export_id' => $this->exportId, 'exception_type' => $exception ? get_class($exception) : null]);
    }
}
