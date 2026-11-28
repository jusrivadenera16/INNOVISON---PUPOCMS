<?php

namespace Tests\Feature;

use App\Http\Controllers\HealthFormsPdfExportController;
use App\Http\Controllers\ReportsController;
use App\Jobs\BuildHealthFormsPdf;
use App\Models\HealthPdfExport;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\HealthFileEncryption;
use App\Services\HealthFormsPdfExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, DB, Queue, Storage};
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\HealthPdfExportFixture;
use Tests\TestCase;

class HealthFormsPdfExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        HealthPdfExportFixture::schema();
        config(['health_pdf_exports.scan_size' => 3, 'health_pdf_exports.batch_size' => 2]);
        $this->actingAs(HealthPdfExportFixture::owner(), 'admin');
    }

    private function start(array $filters = []): HealthPdfExport
    {
        $response = app(HealthFormsPdfExportController::class)->store(Request::create('/admin/reports/health-forms/pdf', 'POST',
            $filters + ['date_from' => '2026-01-01', 'date_to' => '2026-11-28']));
        $this->assertSame(202, $response->status());
        return HealthPdfExport::findOrFail($response->getData(true)['id']);
    }

    private function build(HealthPdfExport $export): HealthPdfExport
    {
        $builder = app(HealthFormsPdfExport::class);
        for ($step = 0; $step < 100 && !$export->terminal(); $step++) {
            $builder->advance($export);
            $export->refresh();
            gc_collect_cycles();
        }
        $this->assertSame('ready', $export->status);
        return $export;
    }

    public function test_create_is_queued_even_when_default_queue_is_sync_and_double_clicks_reuse_it(): void
    {
        Queue::fake();
        $export = $this->start(['condition_keyword' => 'Asthma']);
        $again = $this->start(['condition_keyword' => 'Asthma']);
        $this->assertSame($export->id, $again->id);
        Queue::assertPushed(BuildHealthFormsPdf::class, fn ($job) => $job->connection === 'health_pdf' && $job->queue === 'health-pdf');
        Queue::assertPushed(BuildHealthFormsPdf::class, 1);
        $this->assertStringNotContainsString('Asthma', DB::table('health_pdf_exports')->value('filters'));
        $this->assertStringNotContainsString('Synthetic Export Nurse', DB::table('health_pdf_exports')->value('context'));
    }

    public function test_stale_or_sync_pdf_queue_configuration_fails_closed_instead_of_rendering_in_the_request(): void
    {
        Queue::fake();
        config(['queue.connections.health_pdf.driver' => 'sync']);
        $this->assertSame('health_pdf', (new BuildHealthFormsPdf('unused'))->connection);
        try { $this->start(); $this->fail('PDF must never silently fall back to a sync queue.'); }
        catch (HttpException $exception) { $this->assertSame(503, $exception->getStatusCode()); }
        $this->assertSame(0, HealthPdfExport::count());
        Queue::assertNothingPushed();
    }

    public function test_real_pdf_keeps_every_matching_record_once_in_sorted_order_and_removes_parts(): void
    {
        Queue::fake(); HealthPdfExportFixture::records(7);
        $before = DB::table('health_profiles')->get()->toJson();
        $export = $this->start();
        $builder = app(HealthFormsPdfExport::class);
        while (in_array($export->status, ['queued', 'preparing'], true)) { $builder->advance($export); $export->refresh(); }
        $manifest = Storage::disk('health_private')->get($builder->directory($export).'/manifest.rows');
        $this->assertStringNotContainsString('Synthetic', $manifest);
        $entries = array_map(fn ($line) => json_decode(Crypt::decryptString($line), true), array_filter(explode("\n", $manifest)));
        $this->assertCount(7, $entries);
        $this->assertCount(7, array_unique(array_column($entries, 'id')));
        $this->assertStringContainsString('000001', $entries[0]['row']['name']);
        $this->assertStringContainsString('000007', $entries[6]['row']['name']);
        $export = $this->build($export);
        $this->assertSame(7, $export->rendered);
        $this->assertSame(4, $export->parts);
        $bytes = $builder->downloadBytes($export);
        $this->assertStringStartsWith('%PDF-', $bytes);
        $reader = new Fpdi();
        $this->assertSame($export->context['pages'], $reader->setSourceFile(StreamReader::createByString($bytes)));
        $reader->cleanUp(true);
        $files = Storage::disk('health_private')->files($builder->directory($export));
        $this->assertCount(1, $files);
        $this->assertTrue(app(HealthFileEncryption::class)->isEncrypted(Storage::disk('health_private')->get($files[0])));
        $this->assertSame($before, DB::table('health_profiles')->get()->toJson());
        $response = app(HealthFormsPdfExportController::class)->download($export->id, $builder);
        $this->assertSame($bytes, $response->getContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_empty_range_still_produces_one_pdf(): void
    {
        Queue::fake();
        $export = $this->build($this->start());
        $this->assertSame(0, $export->total);
        $this->assertSame(1, $export->parts);
        $this->assertGreaterThanOrEqual(1, $export->context['pages']);
    }

    public function test_real_dedicated_database_worker_completes_without_using_the_sync_queue(): void
    {
        HealthPdfExportFixture::records(3);
        $export = $this->start();
        $this->assertSame(1, DB::table('health_pdf_jobs')->count());
        $this->assertStringNotContainsString($export->id, DB::table('health_pdf_jobs')->value('payload'));
        $this->artisan('queue:work', ['connection' => 'health_pdf', '--queue' => 'health-pdf', '--stop-when-empty' => true,
            '--sleep' => 0, '--tries' => 1])->assertSuccessful();
        $this->assertSame('ready', $export->fresh()->status);
        $this->assertSame(0, DB::table('health_pdf_jobs')->count());
    }

    public function test_external_sort_merges_more_than_sixteen_runs_without_losing_or_duplicating_rows(): void
    {
        Queue::fake(); HealthPdfExportFixture::records(20);
        config(['health_pdf_exports.scan_size' => 1]);
        $export = $this->start(['sort' => 'name_desc']);
        $builder = app(HealthFormsPdfExport::class);
        for ($step = 0; $step < 21 && in_array($export->status, ['queued', 'preparing'], true); $step++) {
            $builder->advance($export); $export->refresh();
        }
        $this->assertSame('rendering', $export->status);
        $lines = array_filter(explode("\n", Storage::disk('health_private')->get($builder->directory($export).'/manifest.rows')));
        $entries = array_map(fn ($line) => json_decode(Crypt::decryptString($line), true), $lines);
        $this->assertCount(20, $entries);
        $this->assertCount(20, array_unique(array_column($entries, 'id')));
        $this->assertStringContainsString('000020', $entries[0]['row']['name']);
        $this->assertStringContainsString('000001', $entries[19]['row']['name']);
    }

    public function test_date_boundaries_created_at_fallback_and_frozen_upper_id_are_preserved(): void
    {
        Queue::fake(); HealthPdfExportFixture::records(5);
        $ids = HealthProfile::orderBy('id')->pluck('id')->all();
        foreach (['2025-12-31 23:59:59', '2026-01-01 00:00:00', '2026-11-28 23:59:59', '2026-11-29 00:00:00', null] as $index => $date) {
            DB::table('health_profiles')->where('id', $ids[$index])->update(['verified_at' => $date]);
        }
        $export = $this->start();
        $this->assertSame(3, $export->source_total);
        HealthPdfExportFixture::records(1); // A new submission during processing is not silently added to this export.
        $reports = app(ReportsController::class);
        $cursor = 0; $actual = [];
        do {
            $batch = $reports->healthFormsPdfScan($export->filters, $cursor, $export->source_max_id);
            array_push($actual, ...array_column($batch['entries'], 'id')); $cursor = $batch['cursor'];
        } while (!$batch['done']);
        $this->assertEqualsCanonicalizing([$ids[1], $ids[2], $ids[4]], $actual);
    }

    public function test_resume_after_manifest_preparation_does_not_duplicate_saved_rows(): void
    {
        Queue::fake(); HealthPdfExportFixture::records(4);
        $export = $this->start(); $builder = app(HealthFormsPdfExport::class);
        while (in_array($export->status, ['queued', 'preparing'], true)) { $builder->advance($export); $export->refresh(); }
        $export->update(['status' => 'preparing']);
        $builder->advance($export); $export->refresh();
        $this->assertSame(4, $export->total);
        $manifest = Storage::disk('health_private')->get($builder->directory($export).'/manifest.rows');
        $this->assertCount(4, array_filter(explode("\n", $manifest)));
    }

    public function test_new_routes_keep_export_role_module_session_and_throttle_protection(): void
    {
        foreach (['reports.', 'assistant.reports.'] as $prefix) {
            foreach (['store', 'status', 'download'] as $action) {
                $route = app('router')->getRoutes()->getByName($prefix.'health-forms.pdf.'.$action);
                $middleware = $route->gatherMiddleware();
                $this->assertContains('web', $middleware);
                $this->assertContains('admin.guard', $middleware);
                $this->assertContains('module.permission:reports.export_reports', $middleware);
                $this->assertContains('superadmin.export', $middleware);
                $this->assertContains('throttle:60,1,health-pdf', $middleware);
            }
        }
    }

    public function test_filters_are_identical_to_preview_and_reads_are_bounded(): void
    {
        Queue::fake(); HealthPdfExportFixture::records(8);
        HealthProfile::first()->update(['sex' => 'Male', 'course_college' => 'Other', 'has_illness' => 'No', 'medical_history' => []]);
        $reports = app(ReportsController::class);
        foreach ([[], ['course' => 'BSIT', 'gender' => 'female', 'condition' => 'yes', 'status' => 'approved'],
            ['identifier_type' => 'student_number', 'year_level' => '3', 'bmi_categories' => ['normal']],
            ['condition_keyword' => 'Asthma', 'condition_source' => 'health_form', 'condition_match' => 'all'],
            ['user_type' => 'applicant'], ['status' => 'pending']] as $filters) {
            $filters += ['date_from' => '2026-01-01', 'date_to' => '2026-11-28'];
            $expected = (new \ReflectionMethod($reports, 'exportHealthFormsPreviewRecords'))->invoke($reports,
                Request::create('/', 'GET', $filters), \Carbon\Carbon::parse($filters['date_from']), \Carbon\Carbon::parse($filters['date_to']))->pluck('id')->all();
            $bounds = $reports->healthFormsPdfSourceBounds($filters);
            $cursor = 0; $actual = [];
            do {
                $batch = $reports->healthFormsPdfScan($filters, $cursor, $bounds['max_id']);
                $this->assertLessThanOrEqual(3, $batch['scanned']);
                array_push($actual, ...array_column($batch['entries'], 'id'));
                $cursor = $batch['cursor'];
            } while (!$batch['done']);
            $this->assertEqualsCanonicalizing($expected, $actual);
        }
    }

    public function test_another_superadmin_cannot_read_status_or_download_and_revoked_owner_cannot_continue(): void
    {
        Queue::fake(); $export = $this->start();
        $other = User::forceCreate(['name' => 'Other Admin', 'user_role' => 'superadmin']);
        $this->actingAs($other, 'admin');
        try { app(HealthFormsPdfExportController::class)->status($export->id); $this->fail('Cross-account export access must fail.'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) { $this->assertTrue(true); }
        $owner = User::find($export->user_id); $owner->update(['user_role' => 'admin']);
        $this->expectException(\RuntimeException::class);
        app(HealthFormsPdfExport::class)->advance($export);
    }

    public function test_expiry_blocks_download_and_cleanup_never_deletes_patient_files(): void
    {
        Queue::fake(); $export = $this->start();
        $disk = Storage::disk('health_private');
        $disk->put('health_profiles/keep.pdf', 'patient file');
        $disk->put(app(HealthFormsPdfExport::class)->directory($export).'/part-1.pdf', 'temporary');
        $export->update(['expires_at' => now()->subMinute()]);
        try { app(HealthFormsPdfExportController::class)->download($export->id, app(HealthFormsPdfExport::class)); $this->fail('Expired exports must fail.'); }
        catch (HttpException $exception) { $this->assertSame(410, $exception->getStatusCode()); }
        $this->artisan('health-pdf:cleanup')->assertSuccessful();
        $this->assertNull($export->fresh());
        $disk->assertExists('health_profiles/keep.pdf');
        $disk->assertMissing(app(HealthFormsPdfExport::class)->directory($export).'/part-1.pdf');
    }

    public function test_failure_removes_only_this_exports_files_and_clears_sensitive_metadata(): void
    {
        Queue::fake(); $export = $this->start(['condition_keyword' => 'Asthma']);
        $disk = Storage::disk('health_private');
        $disk->put(app(HealthFormsPdfExport::class)->directory($export).'/manifest.rows', 'encrypted');
        (new BuildHealthFormsPdf($export->id))->failed(new \RuntimeException('synthetic failure'));
        $this->assertSame('failed', $export->fresh()->status);
        $this->assertSame([], $export->fresh()->filters);
        $disk->assertMissing(app(HealthFormsPdfExport::class)->directory($export).'/manifest.rows');
    }
}
