<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addAdminHubProfileColumns();
        $this->addAdminEmployeeNumber();
        $this->backfillAdminHubProfiles();
        $this->backfillAdminEmployeeNumbers();
        $this->removeLegacyAdminExternalIdentifier();
    }

    private function addAdminHubProfileColumns(): void
    {
        if (!Schema::hasTable('admin_hub')) {
            return;
        }

        $columns = [
            'employee_number' => fn (Blueprint $table) => $table->string('employee_number')->nullable()->index()->after('admin_uuid'),
            'birthday' => fn (Blueprint $table) => $table->date('birthday')->nullable()->after('email'),
            'age' => fn (Blueprint $table) => $table->unsignedSmallInteger('age')->nullable()->after('birthday'),
            'gender' => fn (Blueprint $table) => $table->string('gender', 50)->nullable()->after('age'),
            'civil_status' => fn (Blueprint $table) => $table->string('civil_status', 50)->nullable()->after('gender'),
            'address' => fn (Blueprint $table) => $table->text('address')->nullable()->after('civil_status'),
            'emergency_contact_person' => fn (Blueprint $table) => $table->string('emergency_contact_person')->nullable()->after('address'),
            'emergency_contact_no' => fn (Blueprint $table) => $table->string('emergency_contact_no', 50)->nullable()->after('emergency_contact_person'),
            'access_level' => fn (Blueprint $table) => $table->string('access_level', 50)->nullable()->after('office'),
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('admin_hub', $column)) {
                Schema::table('admin_hub', $definition);
            }
        }
    }

    private function addAdminEmployeeNumber(): void
    {
        if (!Schema::hasTable('admins') || Schema::hasColumn('admins', 'employee_number')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->index()->after('email');
        });
    }

    private function backfillAdminHubProfiles(): void
    {
        if (!Schema::hasTable('admin_hub')) {
            return;
        }

        $adminColumns = Schema::hasTable('admins') ? Schema::getColumnListing('admins') : [];
        $userColumns = Schema::hasTable('users') ? Schema::getColumnListing('users') : [];

        DB::table('admin_hub')->orderBy('id')->chunkById(100, function ($records) use ($adminColumns, $userColumns) {
            foreach ($records as $record) {
                $user = $this->findLinkedUser($record, $userColumns);
                $admin = $this->findLinkedAdmin($record, $user, $adminColumns);
                $employeeProfile = $this->findEmployeeProfile($record, $user);

                $storedIdentifier = trim((string) ($record->admin_uuid ?? ''));
                $adminExternalIdentifier = $this->value($admin, 'external_identifier');
                $resolvedUuid = $this->firstUuid([
                    $storedIdentifier,
                    $this->value($user, 'student_id'),
                    $adminExternalIdentifier,
                ]);
                if (
                    $resolvedUuid !== null
                    && DB::table('admin_hub')
                        ->where('admin_uuid', $resolvedUuid)
                        ->where('id', '<>', $record->id)
                        ->exists()
                ) {
                    $resolvedUuid = null;
                }
                $employeeNumber = $this->firstNonEmpty([
                    $this->value($record, 'employee_number'),
                    $this->value($admin, 'employee_number'),
                    !$this->isUuid($adminExternalIdentifier) ? $adminExternalIdentifier : null,
                    !$this->isUuid($storedIdentifier) ? $storedIdentifier : null,
                    $this->value($user, 'employee_number'),
                    $this->value($employeeProfile, 'employee_number'),
                ]);
                $birthday = $this->firstNonEmpty([
                    $this->value($record, 'birthday'),
                    $this->value($admin, 'birthday'),
                    $this->value($user, 'DOB'),
                    $this->value($employeeProfile, 'date_of_birth'),
                    $this->value($employeeProfile, 'birthday'),
                ]);
                $age = $this->firstNonEmpty([
                    $this->value($record, 'age'),
                    $this->value($admin, 'age'),
                    $this->value($employeeProfile, 'age'),
                ]) ?: $this->ageFromBirthday($birthday);

                DB::table('admin_hub')->where('id', $record->id)->update([
                    'admin_uuid' => $resolvedUuid,
                    'employee_number' => $employeeNumber,
                    'birthday' => $birthday,
                    'age' => $age !== null && $age !== '' ? (int) $age : null,
                    'gender' => $this->firstNonEmpty([
                        $this->value($record, 'gender'),
                        $this->value($admin, 'gender'),
                        $this->value($user, 'gender'),
                        $this->value($employeeProfile, 'sex'),
                        $this->value($employeeProfile, 'gender'),
                    ]),
                    'civil_status' => $this->firstNonEmpty([
                        $this->value($record, 'civil_status'),
                        $this->value($admin, 'civil_status'),
                        $this->value($employeeProfile, 'civil_status'),
                    ]),
                    'address' => $this->firstNonEmpty([
                        $this->value($record, 'address'),
                        $this->value($admin, 'address'),
                        $this->value($employeeProfile, 'home_address'),
                        $this->value($employeeProfile, 'address'),
                    ]),
                    'emergency_contact_person' => $this->firstNonEmpty([
                        $this->value($record, 'emergency_contact_person'),
                        $this->value($admin, 'emergency_contact_person'),
                        $this->value($employeeProfile, 'emergency_contact_person'),
                    ]),
                    'emergency_contact_no' => $this->firstNonEmpty([
                        $this->value($record, 'emergency_contact_no'),
                        $this->value($admin, 'emergency_contact_no'),
                        $this->value($employeeProfile, 'emergency_contact_no'),
                    ]),
                    'access_level' => 'designee',
                ]);
            }
        });
    }

    private function backfillAdminEmployeeNumbers(): void
    {
        if (!Schema::hasTable('admins') || !Schema::hasColumn('admins', 'employee_number')) {
            return;
        }

        $adminColumns = Schema::getColumnListing('admins');
        $userColumns = Schema::hasTable('users') ? Schema::getColumnListing('users') : [];
        $primaryKey = in_array('admin_id', $adminColumns, true) ? 'admin_id' : 'id';

        DB::table('admins')->orderBy($primaryKey)->chunkById(100, function ($records) use ($primaryKey, $userColumns) {
            foreach ($records as $admin) {
                $externalIdentifier = $this->value($admin, 'external_identifier');
                $user = $this->findUserForAdmin($admin, $userColumns);
                $employeeProfile = $this->findEmployeeProfile($admin, $user);
                $employeeNumber = $this->firstNonEmpty([
                    $this->value($admin, 'employee_number'),
                    !$this->isUuid($externalIdentifier) ? $externalIdentifier : null,
                    $this->value($user, 'employee_number'),
                    $this->value($employeeProfile, 'employee_number'),
                ]);

                if ($employeeNumber !== null) {
                    DB::table('admins')->where($primaryKey, $admin->{$primaryKey})->update([
                        'employee_number' => $employeeNumber,
                    ]);
                }
            }
        }, $primaryKey);
    }

    private function removeLegacyAdminExternalIdentifier(): void
    {
        if (!Schema::hasTable('admins') || !Schema::hasColumn('admins', 'external_identifier')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE admins DROP COLUMN external_identifier');
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('external_identifier');
        });
    }

    private function findLinkedUser(object $record, array $userColumns): ?object
    {
        if ($userColumns === []) {
            return null;
        }

        $userId = $this->value($record, 'user_id');
        if ($userId !== null) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user) {
                return $user;
            }
        }

        $email = strtolower((string) $this->value($record, 'email'));

        return $email !== ''
            ? DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->first()
            : null;
    }

    private function findLinkedAdmin(object $record, ?object $user, array $adminColumns): ?object
    {
        if ($adminColumns === []) {
            return null;
        }

        if (in_array('user_id', $adminColumns, true) && $user) {
            $admin = DB::table('admins')->where('user_id', $user->id)->first();
            if ($admin) {
                return $admin;
            }
        }

        $email = strtolower((string) $this->firstNonEmpty([
            $this->value($record, 'email'),
            $this->value($user, 'email'),
        ]));

        if ($email !== '') {
            foreach (['email', 'email_address'] as $column) {
                if (in_array($column, $adminColumns, true)) {
                    $admin = DB::table('admins')->whereRaw('LOWER(' . $column . ') = ?', [$email])->first();
                    if ($admin) {
                        return $admin;
                    }
                }
            }
        }

        $storedIdentifier = trim((string) $this->value($record, 'admin_uuid'));
        if ($storedIdentifier !== '' && in_array('external_identifier', $adminColumns, true)) {
            return DB::table('admins')->where('external_identifier', $storedIdentifier)->first();
        }

        return null;
    }

    private function findUserForAdmin(object $admin, array $userColumns): ?object
    {
        if ($userColumns === []) {
            return null;
        }

        $userId = $this->value($admin, 'user_id');
        if ($userId !== null) {
            $user = DB::table('users')->where('id', $userId)->first();
            if ($user) {
                return $user;
            }
        }

        $email = strtolower((string) $this->firstNonEmpty([
            $this->value($admin, 'email'),
            $this->value($admin, 'email_address'),
        ]));

        return $email !== ''
            ? DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->first()
            : null;
    }

    private function findEmployeeProfile(object $record, ?object $user): ?object
    {
        if (!Schema::hasTable('health_profile_emp')) {
            return null;
        }

        $columns = Schema::getColumnListing('health_profile_emp');
        if ($user && in_array('user_id', $columns, true)) {
            $profile = DB::table('health_profile_emp')->where('user_id', $user->id)->first();
            if ($profile) {
                return $profile;
            }
        }

        $employeeNumber = $this->firstNonEmpty([
            $this->value($record, 'employee_number'),
            $this->value($user, 'employee_number'),
            !$this->isUuid($this->value($record, 'external_identifier'))
                ? $this->value($record, 'external_identifier')
                : null,
            !$this->isUuid($this->value($record, 'admin_uuid'))
                ? $this->value($record, 'admin_uuid')
                : null,
        ]);

        return $employeeNumber !== null && in_array('employee_number', $columns, true)
            ? DB::table('health_profile_emp')->where('employee_number', $employeeNumber)->first()
            : null;
    }

    private function firstUuid(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($this->isUuid($value)) {
                return $value;
            }
        }

        return null;
    }

    private function isUuid(?string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            trim((string) $value)
        ) === 1;
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function value(?object $record, string $column)
    {
        return $record && property_exists($record, $column) ? $record->{$column} : null;
    }

    private function ageFromBirthday(?string $birthday): ?int
    {
        if (!$birthday) {
            return null;
        }

        try {
            return Carbon::parse($birthday)->age;
        } catch (Throwable) {
            return null;
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'external_identifier')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('external_identifier')->nullable()->after('employee_number');
            });

            if (Schema::hasColumn('admins', 'employee_number')) {
                DB::table('admins')
                    ->whereNull('external_identifier')
                    ->update(['external_identifier' => DB::raw('employee_number')]);
            }
        }

        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'employee_number')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('employee_number');
            });
        }

        if (Schema::hasTable('admin_hub')) {
            $columns = array_values(array_filter([
                'employee_number',
                'birthday',
                'age',
                'gender',
                'civil_status',
                'address',
                'emergency_contact_person',
                'emergency_contact_no',
                'access_level',
            ], fn (string $column) => Schema::hasColumn('admin_hub', $column)));

            if ($columns !== []) {
                Schema::table('admin_hub', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
