<?php

namespace App\Http\Controllers;

use App\Jobs\BuildHealthFormsPdf;
use App\Models\HealthPdfExport;
use App\Models\User;
use App\Services\HealthFormsPdfExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HealthFormsPdfExportController extends Controller
{
    private function actor(): User
    {
        $user = auth('admin')->user() ?? auth()->user();
        abort_unless($user && User::normalizeRole((string) $user->user_role) === User::ROLE_SUPERADMIN
            && $user->canAccessPermission('reports.export_reports') && $user->status !== 'inactive', 403);
        return $user;
    }

    private function owned(string $id): HealthPdfExport
    {
        $actor = $this->actor();
        abort_unless(Str::isUuid($id), 404);
        $export = HealthPdfExport::where('user_id', $actor->id)->findOrFail($id);
        abort_if($export->expires_at->isPast(), 410, 'This PDF export has expired. Please generate it again.');
        return $export;
    }

    private function filters(Request $request): array
    {
        $rules = ['date_from' => 'nullable|date_format:Y-m-d', 'date_to' => 'nullable|date_format:Y-m-d',
            'bmi_categories' => 'nullable|array|max:5', 'bmi_categories.*' => 'string|in:underweight,normal,overweight,obese,no_bmi'];
        foreach (['course', 'user_type', 'gender', 'condition', 'status', 'year_level', 'identifier_type', 'sort', 'condition_keyword', 'condition_source', 'condition_match'] as $field) {
            $rules[$field] = 'nullable|string|max:500';
        }
        $filters = $request->validate($rules);
        $filters['date_from'] = ($filters['date_from'] ?? '') ?: now()->startOfMonth()->toDateString();
        $filters['date_to'] = ($filters['date_to'] ?? '') ?: now()->toDateString();
        if ($filters['date_from'] > $filters['date_to']) [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        $sort = strtolower(trim($filters['sort'] ?? 'name_asc'));
        $filters['sort'] = in_array($sort, ['name_asc', 'name_desc', 'date_asc', 'date_desc'], true) ? $sort : 'name_asc';
        ksort($filters);
        return $filters;
    }

    public function page(Request $request)
    {
        $this->actor();
        $filters = $this->filters($request);
        $exportId = $request->query('pdf_export');
        if ($exportId) $filters = $this->owned((string) $exportId)->filters ?: $filters;
        $prefix = $request->is('assistant/*') ? '/assistant' : '/admin';
        return response()->view('admin.reports.health-forms-pdf-progress', [
            'filters' => $filters, 'exportId' => $exportId,
            'startUrl' => url($prefix.'/reports/health-forms/pdf'),
            'backUrl' => url($prefix.'/reports/export-hub/health-forms').'?'.http_build_query($filters),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request)
    {
        $actor = $this->actor();
        $filters = $this->filters($request);
        abort_unless(Schema::hasTable('health_pdf_exports') && Schema::hasTable('health_pdf_jobs'), 503,
            'PDF background export is not installed yet. The administrator must run the pending migrations.');
        abort_unless(config('queue.connections.health_pdf.driver') === 'database', 503,
            'The PDF background queue is not configured. The administrator must refresh the cached configuration.');
        abort_unless(class_exists(\setasign\Fpdi\Fpdi::class), 503,
            'PDF merging dependencies are not installed. The administrator must install the updated Composer dependencies.');
        try { app(\App\Services\HealthFileEncryption::class)->encrypt('PDF export preflight'); }
        catch (\RuntimeException $exception) { abort(503, 'Private health-file encryption is not configured. Please contact the administrator.'); }
        $export = DB::transaction(function () use ($actor, $filters) {
            // Serializes double clicks across requests without changing the account itself.
            User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            $active = HealthPdfExport::where('user_id', $actor->id)->where('expires_at', '>', now())
                ->whereNotIn('status', ['ready', 'failed'])->first();
            if ($active) {
                abort_unless($active->filters == $filters, 409, 'Another Health Forms PDF is still processing. Please wait for it to finish.');
                return $active;
            }
            $actor->loadMissing('adminProfile');
            $profile = $actor->adminProfile;
            $name = trim((string) $actor->name) ?: 'CLINIC STAFF';
            $credentials = trim((string) ($profile?->report_credentials ?: $actor->report_credentials));
            if ($credentials !== '' && stripos($name, $credentials) === false) $name .= ', '.$credentials;
            $bounds = app(ReportsController::class)->healthFormsPdfSourceBounds($filters);
            $export = HealthPdfExport::create([
                'id' => (string) Str::uuid(), 'user_id' => $actor->id, 'status' => 'queued', 'filters' => $filters,
                'source_max_id' => $bounds['max_id'], 'source_total' => $bounds['total'],
                'context' => ['generated_at' => now()->toIso8601String(), 'headings' => [],
                    'prepared_by' => ['name' => $name, 'position' => trim((string) ($profile?->report_position ?: $actor->report_position))]],
                'expires_at' => now()->addMinutes(config('health_pdf_exports.ttl_minutes', 360)),
            ]);
            BuildHealthFormsPdf::dispatch($export->id);
            return $export;
        });
        return response()->json(['id' => $export->id], 202)->header('Cache-Control', 'no-store, private');
    }

    public function status(string $exportId)
    {
        $export = $this->owned($exportId);
        return response()->json([
            'id' => $export->id, 'status' => $export->status, 'scanned' => $export->scanned,
            'source_total' => $export->source_total, 'total' => $export->total, 'rendered' => $export->rendered,
            'pages' => $export->status === 'ready' ? ($export->context['pages'] ?? 0) : null,
            'expires_at' => $export->expires_at->toIso8601String(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function download(string $exportId, HealthFormsPdfExport $builder)
    {
        $export = $this->owned($exportId);
        abort_unless($export->status === 'ready', 409, 'The PDF is not ready yet.');
        $filters = $export->filters;
        $filename = 'health-forms-'.str_replace('-', '', $filters['date_from']).'-'.str_replace('-', '', $filters['date_to']).'.pdf';
        return response($builder->downloadBytes($export), 200, [
            'Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, private, must-revalidate', 'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
