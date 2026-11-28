<?php

// Synthetic UI fixture: no patient records or application database are read.
require __DIR__.'/../../vendor/autoload.php';
putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('SESSION_DRIVER=array');
putenv('CACHE_STORE=array');
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
$actor = new App\Models\User(['user_role' => App\Models\User::ROLE_SUPERADMIN]);
auth()->shouldUse('admin');
auth()->guard('admin')->setUser($actor);
$source = file_get_contents(resource_path('views/admin/walkin.blade.php'));
$start = strpos($source, '<section class="applicant-reference-verification"');
$end = strpos($source, '</section>', $start) + strlen('</section>');
$verification = substr($source, $start, $end - $start);
$start = strpos($source, '<div class="applicant-approved-result"');
$end = strpos($source, '<div class="applicant-approved-close-row"', $start);
$approved = substr($source, $start, $end - $start);
$markup = Illuminate\Support\Facades\Blade::render($verification.$approved."@include('admin.partials.medical-clearance-modal')", [], true);
$css = '';
foreach (['layouts/admin', 'admin/walkin'] as $view) {
    preg_match_all('~<style\b[^>]*>(.*?)</style>~s', file_get_contents(resource_path('views/'.$view.'.blade.php')), $styles);
    foreach ($styles[1] as $style) {
        $css .= preg_replace('~\{\{.*?\}\}~s', '', $style)."\n";
    }
}
echo json_encode(['markup' => $markup, 'css' => $css], JSON_THROW_ON_ERROR);
