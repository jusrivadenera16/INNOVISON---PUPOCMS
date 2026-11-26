<?php

use Illuminate\Support\Facades\Blade;

$root = dirname(__DIR__, 2);
$reportDir = $root . '/resources/views/admin/reports';
require $root . '/vendor/autoload.php';
$cache = sys_get_temp_dir() . '/reports-csp-check-' . bin2hex(random_bytes(5));
mkdir($cache);
putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('SESSION_DRIVER=array');
putenv('CACHE_DRIVER=array');
putenv('VIEW_COMPILED_PATH=' . $cache);
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['view.compiled' => $cache, 'session.driver' => 'array']);
Illuminate\Support\Carbon::setTestNow(now()->startOfSecond());
$nonce = 'cmVwb3J0cy1jc3AtY2hlY2s=';
request()->attributes->set('csp_nonce', $nonce);

function command(array $arguments, string $input = ''): string
{
    $process = proc_open($arguments, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, dirname(__DIR__, 2));
    fwrite($pipes[0], $input);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) throw new RuntimeException($errors . $output);
    return $output;
}

function pdfLayout(string $html): array
{
    $pdf = new Dompdf\Dompdf(['isRemoteEnabled' => false, 'isPhpEnabled' => false]);
    $pdf->loadHtml($html);
    $pdf->setPaper('A4');
    $pdf->render();
    $frames = [];
    foreach ($pdf->getTree()->get_frames() as $frame) {
        $node = $frame->get_node();
        $style = $frame->get_style();
        if ($node->nodeType !== XML_TEXT_NODE || trim($node->textContent) === '' || $style->display === 'none') continue;
        if (in_array($node->parentNode?->nodeName, ['script', 'style'], true)) continue;
        $frames[] = [trim($node->textContent), array_map(static fn ($n) => round((float) $n, 3), $frame->get_position()),
            round($frame->get_margin_width(), 3), round($frame->get_margin_height(), 3)];
    }
    // Compare drawing commands as well as pagination, excluding generated PDF metadata.
    $bytes = $pdf->output(['compress' => false]);
    $bytes = preg_replace('~/((?:Creation|Mod)Date)\s*\([^)]*\)~', '/$1 (fixed)', $bytes);
    $bytes = preg_replace('~/ID\s*\[\s*<[a-fA-F0-9]+>\s*<[a-fA-F0-9]+>\s*\]~', '/ID [<fixed><fixed>]', $bytes);
    return [$pdf->getCanvas()->get_page_count(), $frames, hash('sha256', $bytes)];
}

try {
    $result = ['sources' => [], 'scripts' => [], 'pdf' => [], 'fixtures' => []];
    $compiler = app('blade.compiler');
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($reportDir));
    foreach ($iterator as $file) {
        if (!str_ends_with($file->getFilename(), '.blade.php')) continue;
        $source = file_get_contents($file->getPathname());
        command([PHP_BINARY, '-l'], $compiler->compileString($source));
        $name = str_replace('\\', '/', substr($file->getPathname(), strlen($reportDir) + 1));
        $result['sources'][$name] = $source;
        if (preg_match('/\s(?:on[a-z]+|style)\s*=/i', $source)) throw new RuntimeException('Inline attribute: ' . $name);
        preg_match_all('~<(?:style|script)\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>~i', $source, $tags);
        foreach ($tags[0] as $tag) {
            if (!str_contains($tag, 'nonce="{{ request()->attributes->get(\'csp_nonce\') }}"')) {
                throw new RuntimeException('Missing nonce: ' . $name);
            }
        }
    }
    foreach ($result['sources'] as $name => $source) {
        preg_match_all('~<script\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>(.*?)</script>~si', $source, $scripts);
        foreach ($scripts[1] as $index => $script) {
            $result['scripts'][$name . ':' . $index] = Blade::render($script, [
                'errors' => new Illuminate\Support\ViewErrorBag(),
                'logbookRouteName' => 'reports.health-forms-logbook',
                'optionGroup' => 'referral-services', 'groupLabel' => 'Referrals',
            ], true);
        }
    }
    $widthCases = [100, 1, 0, -10, 'not-numeric', '5; color:red', INF, NAN, '50', 25.5];
    $result['fixtures']['bars'] = view('admin.reports.partials.appointment-stat-bars', [
        'items' => array_map(static fn ($value) => ['label' => 'Sample </style><script>bad()</script>', 'value' => $value], $widthCases),
        'classes' => ['gold', 'green'],
    ])->render();
    preg_match_all('/class="appointment-chart-fill [^"]* appointment-chart-width-(\d+)"/', $result['fixtures']['bars'], $widths);
    if (array_map('intval', $widths[1]) !== [100, 4, 0, 0, 0, 0, 0, 0, 50, 25]) throw new RuntimeException('Invalid bar widths');
    if (str_contains($result['fixtures']['bars'], '<script>bad()')) throw new RuntimeException('Unescaped chart label');
    $result['fixtures']['emptyBars'] = view('admin.reports.partials.appointment-stat-bars', ['items' => []])->render();

    $specialName = 'A "quoted" O\'Brien <tag> & </script>';
    $buttonData = [
        'clearanceType' => (object) ['id' => 42, 'name' => $specialName],
        'subcategory' => (object) ['id' => 73, 'name' => $specialName],
        'typeCountPlacement' => 'subcategories',
        'typeSourcePayload' => ['source_key' => 'freshmen_applicants', 'filters' => ['name' => $specialName]],
        'subcategorySourcePayload' => null,
        'cond' => (object) ['id' => 8, 'category_id' => 9, 'name' => $specialName],
        'category' => (object) ['name' => $specialName, 'items' => collect([(object) ['name' => $specialName]])],
    ];
    $result['specialName'] = $specialName;
    foreach ([
        'manage-clearance-types.blade.php' => ['data-add-clearance-subcategory', 'data-edit-clearance', 'data-edit-clearance-subcategory'],
        'manage-mar.blade.php' => ['data-edit-condition'],
        'manage-inventory-illness-categories.blade.php' => ['data-view-illness-category'],
    ] as $file => $attributes) {
        foreach ($attributes as $attribute) {
            preg_match_all('~<button\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>~i', $result['sources'][$file], $buttons);
            $tag = null;
            foreach ($buttons[0] as $button) {
                if (preg_match('/\s' . $attribute . '(?=\s|>)/', $button)) {
                    $tag = $button;
                    break;
                }
            }
            if ($tag === null) throw new RuntimeException('Missing button: ' . $attribute);
            $result['fixtures'][$attribute] = Blade::render($tag . '</button>', $buttonData, true);
        }
    }

    $healthData = [
        'reportCourse' => 'Sample Course', 'reportLevel' => 'First Year', 'schoolYear' => '2026-2027',
        'generatedAt' => now(), 'rows' => [[
            'name' => 'Sample Patient', 'address' => 'Sample Address', 'contact' => '1234567', 'guardian' => 'Sample Guardian',
            'vaccination_status' => 'Complete', 'vaccination_doses' => ['Dose 1'], 'illness' => 'Asthma', 'living_with' => 'Family',
        ]],
    ];
    foreach (['health-forms-export-pdf', 'appointment', 'inventory', 'mar', 'audit'] as $type) {
        $file = $type === 'health-forms-export-pdf' ? $type . '.blade.php' : 'print-reports.blade.php';
        $data = $type === 'health-forms-export-pdf' ? $healthData : [
            'title' => 'Sample Report', 'monthFilter' => '2026-11', 'type' => $type, 'isPdf' => true,
            'data' => collect(), 'marClearanceIssuances' => collect(),
        ];
        $baseline = command(['git', 'show', 'HEAD:resources/views/admin/reports/' . $file]);
        $oldHtml = Blade::render($baseline, $data, true);
        $newHtml = Blade::render($result['sources'][$file], $data, true);
        $oldLayout = pdfLayout($oldHtml);
        $newLayout = pdfLayout($newHtml);
        if ($oldLayout !== $newLayout) {
            throw new RuntimeException('PDF layout changed: ' . $type . ' ' . json_encode([$oldLayout, $newLayout]));
        }
        $result['pdf'][$type] = ['pages' => $newLayout[0], 'drawingHash' => $newLayout[2], 'unchanged' => true];
    }
    foreach (['appointment', 'inventory', 'mar'] as $type) {
        $data = ['title' => 'Sample Report', 'monthFilter' => '2026-11', 'type' => $type, 'isPdf' => true];
        if ($type === 'appointment') {
            $data['data'] = collect([(object) ['date' => '2026-11-26', 'name' => 'Sample Patient', 'user_type' => 'Student', 'service' => 'Checkup', 'status' => 'completed']]);
        } elseif ($type === 'inventory') {
            $data['data'] = collect([(object) ['name' => 'Sample Medicine', 'date_added' => now(), 'stock_number' => '1234', 'unit' => 'pcs', 'starting_stock' => 30, 'consumed' => 5, 'current_balance' => 25, 'expiration_date' => now()->addYear()]]);
        } else {
            $data['data'] = collect([(object) ['name' => 'Sample Category', 'medicalConditions' => collect([(object) ['name' => 'Sample Condition', 'consultations' => collect([(object) ['user_role' => 'student', 'user_type' => 'Student']])]])]]);
            $data['marClearanceTypes'] = collect([(object) ['id' => 1, 'name' => 'Sample Clearance', 'allow_direct_use' => true, 'subcategories' => collect([(object) ['id' => 2, 'name' => 'Sample Subcategory']])]]);
            $data['marClearanceIssuances'] = collect([(object) ['clearance_type_id' => 1, 'clearance_subcategory_id' => null, 'user_type' => 'student']]);
            $detail = ['name' => 'Sample Detail', 'student' => 1, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
            $data['marExcusedLetterBreakdown'] = ['type:1' => [$detail], 'subcategory:2' => [$detail]];
        }
        $baseline = command(['git', 'show', 'HEAD:resources/views/admin/reports/print-reports.blade.php']);
        $oldLayout = pdfLayout(Blade::render($baseline, $data, true));
        $newLayout = pdfLayout(Blade::render($result['sources']['print-reports.blade.php'], $data, true));
        if ($oldLayout !== $newLayout) throw new RuntimeException('Populated PDF layout changed: ' . $type);
        $result['pdf'][$type . '-populated'] = ['pages' => $newLayout[0], 'drawingHash' => $newLayout[2], 'unchanged' => true];
    }
    $summary = [
        'views' => count($result['sources']), 'scripts' => count($result['scripts']),
        'fixtures' => count($result['fixtures']), 'pdf' => $result['pdf'],
    ];
    echo json_encode(in_array('--browser-fixtures', $argv, true) ? $result : $summary, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
} finally {
    foreach (glob($cache . '/*') as $file) if (is_file($file)) unlink($file);
    rmdir($cache);
}
