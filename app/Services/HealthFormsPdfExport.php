<?php

namespace App\Services;

use App\Http\Controllers\ReportsController;
use App\Models\HealthPdfExport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class HealthFormsPdfExport
{
    public function directory(HealthPdfExport $export): string
    {
        if (!Str::isUuid($export->id)) throw new RuntimeException('Invalid export identifier.');
        return 'report-exports/health-forms/'.$export->id;
    }

    private function disk()
    {
        return Storage::disk('health_private');
    }

    private function path(HealthPdfExport $export, string $name): string
    {
        $directory = $this->directory($export);
        $this->disk()->makeDirectory($directory);
        @chmod($this->disk()->path($directory), 0700);
        return $this->disk()->path($directory.'/'.$name);
    }

    private function write(string $path, string $bytes): void
    {
        if (file_put_contents($path, $bytes, LOCK_EX) === false) throw new RuntimeException('Cannot write export file.');
        @chmod($path, 0600);
    }

    private function encode(array $entry): string
    {
        return Crypt::encryptString(json_encode($entry, JSON_THROW_ON_ERROR));
    }

    private function decode(string $line): array
    {
        return json_decode(Crypt::decryptString(trim($line)), true, 512, JSON_THROW_ON_ERROR);
    }

    public function advance(HealthPdfExport $export): void
    {
        $user = User::find($export->user_id);
        if (!$user || User::normalizeRole((string) $user->user_role) !== User::ROLE_SUPERADMIN
            || $user->status === 'inactive' || !$user->canAccessPermission('reports.export_reports')) {
            throw new RuntimeException('Export access is no longer available.');
        }
        if ($export->expires_at->isPast() || $export->terminal()) return;
        $request = app('request');
        $cacheKey = HealthRecordSchemaCache::REQUEST_ATTRIBUTE;
        $previousCache = $request->attributes->get($cacheKey);
        $request->attributes->set($cacheKey, new HealthRecordSchemaCache());
        try {
            match ($export->status) {
                'queued', 'preparing' => $this->prepare($export),
                'rendering' => $this->render($export),
                'merging' => $this->merge($export),
                default => throw new RuntimeException('Invalid export state.'),
            };
        } finally {
            // Metadata only, stage-local. Never retain decrypted models between jobs.
            if ($previousCache === null) $request->attributes->remove($cacheKey);
            else $request->attributes->set($cacheKey, $previousCache);
        }
    }

    private function compare(array $a, array $b, string $sort): int
    {
        $comparison = str_starts_with($sort, 'date_')
            ? $a['date_key'] <=> $b['date_key'] : strnatcasecmp($a['name_key'], $b['name_key']);
        if (str_ends_with($sort, '_desc')) $comparison *= -1;
        return $comparison ?: ($a['id'] <=> $b['id']);
    }

    private function prepare(HealthPdfExport $export): void
    {
        $data = app(ReportsController::class)->healthFormsPdfScan($export->filters, $export->cursor_id, $export->source_max_id);
        $context = $export->context;
        $sort = $export->filters['sort'] ?? 'name_asc';
        $entries = $data['entries'];
        usort($entries, fn ($a, $b) => $this->compare($a, $b, $sort));
        $run = 'run-'.str_pad((string) $export->cursor_id, 12, '0', STR_PAD_LEFT).'.rows';
        $this->write($this->path($export, $run), implode('', array_map(fn ($entry) => $this->encode($entry)."\n", $entries)));
        foreach ($entries as $entry) {
            // At most one distinct value plus a mixed marker per heading, not an unbounded set.
            foreach (['course', 'school_year', 'level'] as $key) {
                if ($entry[$key] === '') continue;
                if (!array_key_exists($key, $context['headings'])) $context['headings'][$key] = $entry[$key];
                elseif ($context['headings'][$key] !== $entry[$key]) $context['headings'][$key] = null;
            }
        }
        $context['peak_bytes'] = max($context['peak_bytes'] ?? 0, memory_get_peak_usage(true));
        $export->fill(['status' => 'preparing', 'cursor_id' => $data['cursor'], 'scanned' => $export->scanned + $data['scanned'],
            'total' => $export->total + count($entries), 'context' => $context])->save();
        if ($data['done']) {
            $this->sortRuns($export, $sort);
            $export->update(['status' => 'rendering']);
        }
    }

    private function sortRuns(HealthPdfExport $export, string $sort): void
    {
        $files = array_values(array_filter($this->disk()->files($this->directory($export)), fn ($file) => str_starts_with(basename($file), 'run-')));
        $paths = array_map(fn ($file) => $this->disk()->path($file), $files);
        $round = 0;
        // External merge sort: only 16 decrypted heads and 16 handles live at once.
        // Keep original runs until the manifest is complete, so a killed preparation can retry.
        do {
            $next = [];
            foreach (array_chunk($paths, 16) as $index => $group) {
                $output = $this->path($export, 'sort-'.$round.'-'.$index.'.rows');
                $this->mergeRuns($group, $output, $sort);
                $next[] = $output;
            }
            $paths = $next;
            $round++;
        } while (count($paths) > 1);
        $manifest = $this->path($export, 'manifest.rows');
        if ($paths) {
            if (!copy($paths[0], $manifest)) throw new RuntimeException('Cannot finalize export manifest.');
            @chmod($manifest, 0600);
        } else $this->write($manifest, '');
        // Intermediate files remain until success/failure cleanup: they contain encrypted records only.
    }

    private function mergeRuns(array $inputs, string $output, string $sort): void
    {
        $handles = []; $heads = [];
        $out = fopen($output, 'wb');
        if (!$out) throw new RuntimeException('Cannot open sorted export file.');
        @chmod($output, 0600);
        try {
            foreach ($inputs as $index => $path) {
                $handles[$index] = fopen($path, 'rb');
                if (!$handles[$index]) throw new RuntimeException('Cannot read export records.');
                if (($line = fgets($handles[$index])) !== false) $heads[$index] = [$this->decode($line), $line];
            }
            while ($heads) {
                $chosen = array_key_first($heads);
                foreach ($heads as $index => $head) {
                    if ($this->compare($head[0], $heads[$chosen][0], $sort) < 0) $chosen = $index;
                }
                if (fwrite($out, $heads[$chosen][1]) === false) throw new RuntimeException('Cannot sort export records.');
                if (($line = fgets($handles[$chosen])) !== false) $heads[$chosen] = [$this->decode($line), $line];
                else unset($heads[$chosen]);
            }
        } finally {
            foreach ($handles as $handle) if (is_resource($handle)) fclose($handle);
            fclose($out);
        }
    }

    private function render(HealthPdfExport $export): void
    {
        $manifest = fopen($this->path($export, 'manifest.rows'), 'rb');
        if (!$manifest) throw new RuntimeException('Cannot read export manifest.');
        $rows = [];
        try {
            if (fseek($manifest, $export->manifest_offset) !== 0) throw new RuntimeException('Cannot resume export.');
            $size = max(1, min(100, (int) config('health_pdf_exports.batch_size', 25)));
            while (count($rows) < $size && ($line = fgets($manifest)) !== false) $rows[] = $this->decode($line)['row'];
            $offset = ftell($manifest);
        } finally { fclose($manifest); }
        $last = $export->rendered + count($rows) >= $export->total;
        $context = $export->context;
        $from = Carbon::parse($export->filters['date_from']);
        $to = Carbon::parse($export->filters['date_to']);
        $headings = $context['headings'] ?? [];
        if ($export->parts === 0) {
            // One shared letterhead template keeps logos/fonts from being embedded once per batch.
            $letterhead = Pdf::loadView('admin.reports.health-forms-export-pdf', [
                'letterheadOnly' => true, 'preparedByIdentityOverride' => $context['prepared_by'],
            ])->setPaper('a4', 'portrait')->output();
            $this->write($this->path($export, 'letterhead.pdf'), app(HealthFileEncryption::class)->encrypt($letterhead));
            unset($letterhead);
            gc_collect_cycles();
        }
        $bytes = Pdf::loadView('admin.reports.health-forms-export-pdf', [
            'rows' => $rows, 'reportCourse' => ($export->filters['course'] ?? '') ?: ($headings['course'] ?? 'All Courses'),
            'reportLevel' => $headings['level'] ?? '',
            'schoolYear' => $headings['school_year'] ?? ($from->format('Y').($from->year !== $to->year ? '-'.$to->format('Y') : '')),
            'dateFrom' => $from, 'dateTo' => $to, 'generatedAt' => Carbon::parse($context['generated_at']),
            'preparedByIdentityOverride' => $context['prepared_by'], 'showSignatures' => $last,
            'showLetterhead' => false,
        ])->setPaper('a4', 'portrait')->output();
        $this->write($this->path($export, 'part-'.($export->parts + 1).'.pdf'), app(HealthFileEncryption::class)->encrypt($bytes));
        unset($bytes);
        $context['peak_bytes'] = max($context['peak_bytes'] ?? 0, memory_get_peak_usage(true));
        $export->update(['rendered' => $export->rendered + count($rows), 'parts' => $export->parts + 1,
            'manifest_offset' => $offset, 'status' => $last ? 'merging' : 'rendering', 'context' => $context]);
    }

    private function merge(HealthPdfExport $export): void
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        try {
            $letterhead = app(HealthFileEncryption::class)->decrypt(file_get_contents($this->path($export, 'letterhead.pdf')));
            $pdf->setSourceFile(StreamReader::createByString($letterhead, strlen($letterhead) + 1));
            $letterheadTemplate = $pdf->importPage(1);
            unset($letterhead);
            for ($part = 1; $part <= $export->parts; $part++) {
                $bytes = app(HealthFileEncryption::class)->decrypt(file_get_contents($this->path($export, 'part-'.$part.'.pdf')));
                // Keep parser streams in RAM, not plaintext system temp files.
                $reader = StreamReader::createByString($bytes, strlen($bytes) + 1);
                $pages = $pdf->setSourceFile($reader);
                for ($page = 1; $page <= $pages; $page++) {
                    $template = $pdf->importPage($page);
                    $size = $pdf->getTemplateSize($template);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($letterheadTemplate);
                    $pdf->useTemplate($template);
                }
                unset($bytes);
            }
            $bytes = $pdf->Output('S');
            $this->write($this->path($export, 'report.pdf'), app(HealthFileEncryption::class)->encrypt($bytes));
            $context = $export->context;
            $context['peak_bytes'] = max($context['peak_bytes'] ?? 0, memory_get_peak_usage(true));
            $context['pages'] = $pdf->PageNo();
            $context['output_bytes'] = strlen($bytes);
            $export->update(['status' => 'ready', 'filters' => $export->filters, 'context' => $context]);
        } finally { $pdf->cleanUp(true); }
        foreach ($this->disk()->files($this->directory($export)) as $file) {
            if (basename($file) !== 'report.pdf') $this->disk()->delete($file);
        }
    }

    public function downloadBytes(HealthPdfExport $export): string
    {
        return app(HealthFileEncryption::class)->decrypt($this->disk()->get($this->directory($export).'/report.pdf'));
    }

    public function discardFiles(HealthPdfExport $export): void
    {
        // UUID validation above fixes cleanup to this export, never patient document directories.
        $this->disk()->deleteDirectory($this->directory($export));
    }
}
