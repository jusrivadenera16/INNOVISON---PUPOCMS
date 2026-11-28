<?php

// Standalone, synthetic-only benchmark. It never connects to the production database.
require dirname(__DIR__, 2).'/vendor/autoload.php';
putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('CACHE_DRIVER=array');
putenv('SESSION_DRIVER=array');
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Tests\Support\HealthPdfExportFixture::schema();
$options = getopt('', ['records:', 'mode:']);
$count = max(0, min(10000, (int) ($options['records'] ?? 1500)));
$mode = $options['mode'] ?? 'batch';
fwrite(STDERR, 'Seeding '.$count.' synthetic records into isolated in-memory SQLite.'.PHP_EOL);
Tests\Support\HealthPdfExportFixture::records($count);
$owner = Tests\Support\HealthPdfExportFixture::owner();
auth('admin')->login($owner);
Illuminate\Support\Facades\Queue::fake();
$filters = ['date_from' => '2026-01-01', 'date_to' => '2026-11-28', 'sort' => 'name_asc'];
$directory = base_path('tmp/pdfs');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$path = $directory.'/health-forms-'.$mode.'-'.$count.'.pdf';
$start = microtime(true);
memory_reset_peak_usage();
if ($mode === 'legacy') {
    $controller = app(App\Http\Controllers\ReportsController::class);
    $records = (new ReflectionMethod($controller, 'exportHealthFormsPreviewRecords'))->invoke($controller,
        Illuminate\Http\Request::create('/', 'GET', $filters), Carbon\Carbon::parse($filters['date_from']), Carbon\Carbon::parse($filters['date_to']));
    $response = (new ReflectionMethod($controller, 'streamHealthFormsPdf'))->invoke($controller,
        $records, Carbon\Carbon::parse($filters['date_from']), Carbon\Carbon::parse($filters['date_to']), '');
    file_put_contents($path, $response->getContent());
    $result = ['mode' => $mode, 'rows' => $count];
} else {
    $response = app(App\Http\Controllers\HealthFormsPdfExportController::class)->store(
        Illuminate\Http\Request::create('/admin/reports/health-forms/pdf', 'POST', $filters));
    $export = App\Models\HealthPdfExport::findOrFail($response->getData(true)['id']);
    $builder = app(App\Services\HealthFormsPdfExport::class);
    for ($step = 0; !$export->terminal() && $step < 20000; $step++) {
        $builder->advance($export);
        $export->refresh();
        gc_collect_cycles();
        if ($step % 25 === 0) fwrite(STDERR, json_encode(['stage' => $export->status, 'matched' => $export->total, 'rendered' => $export->rendered]).PHP_EOL);
    }
    if ($export->status !== 'ready') throw new RuntimeException('Synthetic benchmark did not complete.');
    file_put_contents($path, $builder->downloadBytes($export));
    $result = ['mode' => $mode, 'rows' => $export->rendered, 'parts' => $export->parts, 'pages' => $export->context['pages']];
    $builder->discardFiles($export);
}
$result += ['seconds' => round(microtime(true) - $start, 2), 'peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
    'output_bytes' => filesize($path), 'output' => $path];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
