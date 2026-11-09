<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AdminHub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeveloperTestAccountController extends Controller
{
    private const ACCOUNT_TYPES = [
        'applicant' => 'Applicant',
        'student' => 'Student',
        'dependent' => 'Dependent',
        'faculty' => 'Faculty',
        'admin' => 'Non-teaching Staff / Admin Designee',
    ];

    public static function isToolEnabled(): bool
    {
        if (!(bool) config('services.test_account_creation.enabled', false)) {
            return false;
        }

        $host = strtolower(trim((string) request()->getHost()));
        $localHosts = ['localhost', '127.0.0.1', '::1'];

        if (app()->environment('local') && in_array($host, $localHosts, true)) {
            return true;
        }

        $appUrlHost = strtolower(trim((string) parse_url((string) config('app.url'), PHP_URL_HOST)));
        if ($appUrlHost === '' || !hash_equals($appUrlHost, $host)) {
            return false;
        }

        return !in_array($host, $localHosts, true)
            && in_array($host, (array) config('services.test_account_creation.allowed_hosts', []), true);
    }

    public function index()
    {
        $this->authorizeTool();

        return view('admin.test_acc_reg', [
            'accountTypes' => self::ACCOUNT_TYPES,
            'testAccounts' => User::query()
                ->where('student_id', 'like', 'TEST-%')
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->authorizeTool();

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix_name' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'account_type' => ['required', Rule::in(array_keys(self::ACCOUNT_TYPES))],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.min' => 'Use at least 8 characters for the test account password.',
            'password.regex' => 'The password must include uppercase and lowercase letters, a number, and a symbol.',
        ]);

        if ($validated['account_type'] === 'admin' && !Schema::hasTable('admin_hub')) {
            throw ValidationException::withMessages([
                'account_type' => 'Admin Hub is not available, so a non-teaching test account cannot be created.',
            ]);
        }

        $accountType = $validated['account_type'];
        $userRole = $accountType === 'admin' ? User::ROLE_ADMIN : User::ROLE_STUDENT;
        $userType = match ($accountType) {
            'applicant' => 'Applicant',
            'dependent' => 'Dependent',
            'faculty' => 'Faculty',
            'admin' => 'Admin',
            default => 'Student',
        };
        $fullName = trim(implode(' ', array_filter([
            trim($validated['first_name']),
            trim((string) ($validated['middle_name'] ?? '')),
            trim($validated['last_name']),
            trim((string) ($validated['suffix_name'] ?? '')),
        ], static fn ($part) => $part !== '')));

        $user = DB::transaction(function () use ($validated, $actor, $accountType, $userRole, $userType, $fullName, $request) {
            $studentId = $this->newTestIdentifier();
            $attributes = [
                'student_id' => $studentId,
                'first_name' => trim($validated['first_name']),
                'middle_name' => trim((string) ($validated['middle_name'] ?? '')) ?: null,
                'last_name' => trim($validated['last_name']),
                'suffix_name' => trim((string) ($validated['suffix_name'] ?? '')) ?: null,
                'name' => $fullName,
                'email' => $validated['email'],
                'user_role' => $userRole,
                'user_type' => $userType,
                'status' => 'active',
                'password' => Hash::make($validated['password']),
            ];

            if ($accountType === 'student') {
                $attributes['student_type'] = 'regular';
            }
            $attributes['applicant_type'] = $accountType === 'applicant' ? 'new' : null;

            foreach (array_keys($attributes) as $column) {
                if (!Schema::hasColumn('users', $column)) {
                    unset($attributes[$column]);
                }
            }

            $user = User::create($attributes);

            if ($accountType === 'admin') {
                $adminHubAttributes = [
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'suffix_name' => $user->suffix_name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'admin_designee',
                    'status' => 'active',
                    'access_level' => 'designee',
                ];

                foreach (array_keys($adminHubAttributes) as $column) {
                    if (!AdminHub::hasColumn($column)) {
                        unset($adminHubAttributes[$column]);
                    }
                }

                AdminHub::create($adminHubAttributes);
            }

            if (Schema::hasTable('activity_logs')) {
                try {
                    ActivityLog::create([
                        'user_id' => $actor->id,
                        'user_name' => $actor->name ?: $actor->email,
                        'action' => 'Test Account Created',
                        'description' => sprintf('Created a %s test account (%s).', self::ACCOUNT_TYPES[$accountType], $user->email),
                        'ip_address' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    ]);
                } catch (\Throwable) {
                    // Account creation remains available if optional audit columns differ by deployment.
                }
            }

            return $user;
        });

        return redirect()
            ->route('admin.developer-tools.test-accounts')
            ->with('createdTestAccount', [
                'name' => $user->name,
                'email' => $user->email,
                'account_type' => self::ACCOUNT_TYPES[$accountType],
                'student_id' => $user->student_id,
            ]);
    }

    public function update(Request $request, int $userId)
    {
        $actor = $this->authorizeTool();
        $user = $this->findTestAccount($userId);

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix_name' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'account_type' => ['required', Rule::in(array_keys(self::ACCOUNT_TYPES))],
            'password' => [
                'nullable',
                'confirmed',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.min' => 'Use at least 8 characters for the test account password.',
            'password.regex' => 'The password must include uppercase and lowercase letters, a number, and a symbol.',
        ]);

        if ($validated['account_type'] === 'admin' && !Schema::hasTable('admin_hub')) {
            throw ValidationException::withMessages([
                'account_type' => 'Admin Hub is not available, so a non-teaching test account cannot be created.',
            ]);
        }

        $accountType = $validated['account_type'];
        DB::transaction(function () use ($validated, $actor, $accountType, $user, $request) {
            $firstName = trim($validated['first_name']);
            $middleName = trim((string) ($validated['middle_name'] ?? '')) ?: null;
            $lastName = trim($validated['last_name']);
            $suffixName = trim((string) ($validated['suffix_name'] ?? '')) ?: null;
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->name = trim(implode(' ', array_filter([
                $firstName,
                $middleName,
                $lastName,
                $suffixName,
            ], static fn ($part) => trim((string) $part) !== '')));
            $user->email = $validated['email'];
            $user->user_role = $accountType === 'admin' ? User::ROLE_ADMIN : User::ROLE_STUDENT;
            $user->user_type = match ($accountType) {
                'applicant' => 'Applicant',
                'dependent' => 'Dependent',
                'faculty' => 'Faculty',
                'admin' => 'Admin',
                default => 'Student',
            };

            foreach (['middle_name' => $middleName, 'suffix_name' => $suffixName] as $column => $value) {
                if (Schema::hasColumn('users', $column)) {
                    $user->setAttribute($column, $value);
                }
            }

            if (Schema::hasColumn('users', 'student_type')) {
                $user->student_type = $accountType === 'student' ? 'regular' : null;
            }
            if (Schema::hasColumn('users', 'applicant_type')) {
                $user->applicant_type = $accountType === 'applicant' ? 'new' : null;
            }

            if (filled($validated['password'] ?? null)) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $this->syncAdminDesigneeProfile($user, $accountType === 'admin');
            $this->recordToolActivity($actor, 'Test Account Updated', sprintf('Updated test account %s (%s).', $user->student_id, $user->email), $request);
        });

        return redirect()->route('admin.developer-tools.test-accounts')->with('updatedTestAccount', true);
    }

    public function destroy(Request $request, int $userId)
    {
        $actor = $this->authorizeTool();
        $user = $this->findTestAccount($userId);

        if ($this->hasLinkedRecords($user)) {
            return redirect()->route('admin.developer-tools.test-accounts')->with(
                'testAccountError',
                'This test account already has linked clinic records, so it cannot be deleted from this tool.'
            );
        }

        DB::transaction(function () use ($user, $actor, $request) {
            if (Schema::hasTable('admin_hub') && AdminHub::hasColumn('user_id')) {
                AdminHub::query()
                    ->where('user_id', $user->id)
                    ->whereIn('role', ['admin_designee', 'designee'])
                    ->delete();
            }

            $description = sprintf('Deleted test account %s (%s).', $user->student_id, $user->email);
            $user->delete();
            $this->recordToolActivity($actor, 'Test Account Deleted', $description, $request);
        });

        return redirect()->route('admin.developer-tools.test-accounts')->with('deletedTestAccount', true);
    }

    private function authorizeTool(): User
    {
        abort_unless(self::isToolEnabled(), 404);

        $user = Auth::guard('admin')->user() ?? Auth::user();
        $developerEmail = strtolower(trim((string) config('app.system_developer_email', 'pupocms2027@gmail.com')));
        $userEmail = strtolower(trim((string) ($user?->email ?? '')));

        abort_unless(
            $user instanceof User
                && User::normalizeRole((string) $user->user_role) === User::ROLE_SUPERADMIN
                && $developerEmail !== ''
                && $userEmail !== ''
                && hash_equals($developerEmail, $userEmail),
            404
        );

        return $user;
    }

    private function newTestIdentifier(): string
    {
        do {
            $identifier = 'TEST-' . Str::upper(Str::random(10));
        } while (User::where('student_id', $identifier)->exists());

        return $identifier;
    }

    private function findTestAccount(int $userId): User
    {
        return User::query()
            ->whereKey($userId)
            ->where('student_id', 'like', 'TEST-%')
            ->firstOrFail();
    }

    private function syncAdminDesigneeProfile(User $user, bool $isDesignee): void
    {
        if (!Schema::hasTable('admin_hub') || !AdminHub::hasColumn('user_id')) {
            return;
        }

        $query = AdminHub::query()->where('user_id', $user->id);
        if (!$isDesignee) {
            $query->whereIn('role', ['admin_designee', 'designee'])->delete();
            return;
        }

        $profile = $query->first() ?? new AdminHub();
        $attributes = [
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'suffix_name' => $user->suffix_name,
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin_designee',
            'status' => 'active',
            'access_level' => 'designee',
        ];

        foreach ($attributes as $column => $value) {
            if (AdminHub::hasColumn($column)) {
                $profile->setAttribute($column, $value);
            }
        }

        $profile->save();
    }

    private function hasLinkedRecords(User $user): bool
    {
        $ignoredTables = ['users', 'admin_hub', 'activity_logs', 'migrations'];

        foreach (Schema::getTableListing() as $table) {
            $tableName = (string) $table;
            if (str_contains($tableName, '.')) {
                $tableName = substr($tableName, strrpos($tableName, '.') + 1);
            }

            if (in_array($tableName, $ignoredTables, true)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            foreach ([
                'user_id' => $user->id,
                'student_id' => $user->student_id,
                'student_number' => $user->student_id,
                'reference_number' => $user->student_id,
                'employee_number' => $user->student_id,
                'email' => $user->email,
            ] as $column => $value) {
                if (in_array($column, $columns, true) && DB::table($table)->where($column, $value)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function recordToolActivity(User $actor, string $action, string $description, Request $request): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        try {
            ActivityLog::create([
                'user_id' => $actor->id,
                'user_name' => $actor->name ?: $actor->email,
                'action' => $action,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (\Throwable) {
            // Keep test-account management available if optional audit columns differ by deployment.
        }
    }
}
