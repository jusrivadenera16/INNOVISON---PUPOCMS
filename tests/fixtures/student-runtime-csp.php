<?php

// Opt-in CLI fixture capture only. All accounts are synthetic and database writes are rolled back.
if (PHP_SAPI !== 'cli' || getenv('CSP_CAPTURE_STUDENT_PAGES') !== '1') {
    throw new RuntimeException('Set CSP_CAPTURE_STUDENT_PAGES=1 to capture local student fixtures.');
}
$root = dirname(__DIR__, 2);
putenv('APP_ENV=testing');
putenv('SESSION_DRIVER=array');
putenv('CACHE_STORE=array');
putenv('CACHE_DRIVER=array');
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$origin = 'http://127.0.0.1:8000';
$app->instance('request', Illuminate\Http\Request::create($origin));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array', 'services.idp.enabled' => false, 'health_data_encryption.enabled' => true]);
$host = config('database.connections.'.config('database.default').'.host');
if (!in_array($host, ['localhost', '127.0.0.1', '::1'], true)) throw new RuntimeException('Local test database required.');
Illuminate\Support\Facades\Http::fake(fn () => Illuminate\Support\Facades\Http::response(['data' => []], 200));
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\Notification::fake();
Illuminate\Support\Facades\Queue::fake();
Illuminate\Support\Facades\URL::forceRootUrl($origin);
$directory = storage_path('app/csp-student-runtime');
if (!is_dir($directory)) mkdir($directory, 0700, true);
$results = [];
Illuminate\Support\Facades\DB::beginTransaction();
try {
    $columns = array_flip(Illuminate\Support\Facades\Schema::getColumnListing('users'));
    $actors = [];
    foreach ([
        'student' => ['user_type' => 'Student', 'student_type' => 'regular', 'student_number' => '2026-99998-TG-0'],
        'applicant' => ['user_type' => 'Applicant', 'applicant_type' => 'new'],
        'transferee' => ['user_type' => 'Applicant', 'applicant_type' => 'transferee'],
        'faculty' => ['user_type' => 'Faculty', 'idp_role' => 'faculty'],
        'staff' => ['user_type' => 'Admin', 'idp_role' => 'staff'],
        'dependent' => ['user_type' => 'Dependent'],
        'setup' => ['user_type' => null, 'student_type' => null],
        'existing' => ['user_type' => 'Student', 'student_type' => 'regular', 'is_health_profile_completed' => true],
    ] as $key => $attributes) {
        $data = array_merge([
            'first_name' => 'CSP', 'last_name' => 'Fixture', 'name' => 'CSP Fixture',
            'email' => 'csp-'.bin2hex(random_bytes(5)).'@example.test',
            'student_id' => 'CSP-'.bin2hex(random_bytes(6)),
            'password' => password_hash(bin2hex(random_bytes(20)), PASSWORD_BCRYPT),
            'user_role' => 'student', 'status' => 'active', 'is_health_profile_completed' => false,
            'DOB' => '2005-01-01', 'gender' => 'Female', 'contact_no' => '09123456789',
        ], $attributes);
        $actors[$key] = App\Models\User::withoutEvents(fn () => App\Models\User::forceCreate(array_intersect_key($data, $columns)));
    }
    App\Models\HealthProfile::forceCreate([
        'user_id' => $actors['existing']->id,
        'course_code' => 'BSIT',
        'clearance_status' => 'Issued',
        'home_address' => 'Synthetic fixture address',
    ]);
    $cases = [
        ['guest-landing', null, '/'],
        ['guest-home', null, '/student/home'],
        ['guest-faq', null, '/student/faq'],
        ['guest-booking', null, '/student/booking'],
        ['guest-login', null, '/login'],
        ['student-home', 'student', '/student/home'],
        ['student-booking', 'student', '/student/booking'],
        ['student-profile', 'student', '/student/account'],
        ['student-health-record', 'student', '/student/account?view=health-record'],
        ['student-notifications', 'student', '/student/account?view=notifications'],
        ['student-history', 'student', '/student/history'],
        ['student-faq', 'student', '/student/faq'],
        ['student-health-form', 'student', '/student/health-form/student'],
        ['student-evaluation', 'student', '/student/evaluation-form/preview'],
        ['applicant-home', 'applicant', '/student/home'],
        ['applicant-health-form', 'applicant', '/student/health-form'],
        ['transferee-health-form', 'transferee', '/student/health-form/transferee'],
        ['faculty-health-form', 'faculty', '/student/health-form/employee'],
        ['faculty-home', 'faculty', '/student/home'],
        ['faculty-profile', 'faculty', '/student/account'],
        ['staff-health-form', 'staff', '/student/health-form/employee'],
        ['dependent-form', 'dependent', '/student/health-form/employee'],
        ['dependent-home', 'dependent', '/student/home'],
        ['dependent-profile', 'dependent', '/student/account'],
        ['first-setup', 'setup', '/student/home'],
        ['existing-health-record', 'existing', '/student/account?view=health-record'],
    ];
    foreach ($cases as [$name, $actor, $path]) {
        Illuminate\Support\Facades\Auth::forgetGuards();
        Illuminate\Support\Facades\Auth::shouldUse('student');
        if ($actor !== null) {
            Illuminate\Support\Facades\Auth::guard('student')->setUser($actors[$actor]);
            Illuminate\Support\Facades\Auth::guard('web')->setUser($actors[$actor]);
        }
        $request = Illuminate\Http\Request::create($origin.$path, 'GET');
        $response = $kernel->handle($request);
        $file = $name.'.html';
        file_put_contents($directory.'/'.$file, $response->getContent());
        $results[] = ['name' => $name, 'actor' => $actor, 'path' => $path, 'status' => $response->getStatusCode(),
            'location' => $response->headers->get('Location'), 'file' => $file,
            'policy' => $response->headers->get('Content-Security-Policy')];
        echo $name.' '.$response->getStatusCode().PHP_EOL;
        $kernel->terminate($request, $response);
    }
} finally {
    while (Illuminate\Support\Facades\DB::transactionLevel() > 0) Illuminate\Support\Facades\DB::rollBack();
    file_put_contents($directory.'/manifest.json', json_encode($results, JSON_THROW_ON_ERROR));
}
