<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_STUDENT = 'student';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_SUPER_ADMIN = self::ROLE_SUPERADMIN; // Backward-compatible alias.
    public const ROLE_STUDENT_ASSISTANT = self::ROLE_ADMIN; // Backward-compatible alias.

    public static function normalizeRole(?string $role): string
    {
        $normalizedRole = strtolower(trim((string) $role));

        return match ($normalizedRole) {
            'superadmin', 'super_admin' => self::ROLE_SUPERADMIN,
            'admin', 'student_assistant', 'studentassistant', 'assistant', 'nurse' => self::ROLE_ADMIN,
            default => $normalizedRole,
        };
    }

    public static function userTypeForIdpRole(?string $role): string
    {
        $role = str_replace(['-', ' '], '_', strtolower(trim((string) $role)));

        return match ($role) {
            'student', 'ojt', 'student_ojt' => 'Student',
            'applicant' => 'Applicant',
            'faculty' => 'Faculty',
            'admin', 'staff', 'employee', 'designee', 'admin_designee',
            'non_teaching', 'non_teaching_staff' => 'Admin',
            'guest' => 'Guest',
            default => 'Dependent',
        };
    }

    public const CLINIC_ACCOUNT_TYPES = [
        'applicant' => 'Applicant',
        'student' => 'Student / OJT',
        'faculty' => 'Faculty',
        'non_teaching_staff' => 'Non-teaching Staff / Admins',
        'dependent' => 'Guest',
    ];

    public function needsClinicAccountTypeSelection(): bool
    {
        return self::normalizeRole($this->user_role) === self::ROLE_STUDENT
            && $this->clinicAccountTypeKey() === null;
    }

    public static function userTypeForClinicAccountType(string $type): ?string
    {
        return match ($type) {
            'applicant' => 'Applicant',
            'student' => 'Student',
            'faculty' => 'Faculty',
            'non_teaching_staff' => 'Admin',
            'dependent' => 'Dependent',
            default => null,
        };
    }

    public function clinicAccountTypeKey(): ?string
    {
        $userType = strtolower(trim((string) $this->user_type));

        return match ($userType) {
            'applicant' => 'applicant',
            'student', 'ojt', 'student / ojt' => 'student',
            'faculty' => 'faculty',
            'admin', 'staff', 'employee', 'non-teaching staff', 'non-teaching staff / admins' => 'non_teaching_staff',
            'guest', 'dependent' => 'dependent',
            default => null,
        };
    }

    public function allowedClinicAccountTypes(): array
    {
        if ($this->hasPendingAdmissionReference()) {
            return ['applicant'];
        }

        $role = str_replace(['-', ' '], '_', strtolower(trim((string) $this->idp_role)));
        $type = match ($role) {
            'applicant' => 'applicant',
            'student', 'ojt', 'student_ojt' => 'student',
            'faculty' => 'faculty',
            'admin', 'staff', 'employee', 'designee', 'admin_designee',
            'non_teaching', 'non_teaching_staff' => 'non_teaching_staff',
            'guest', 'dependent', 'dependents' => 'dependent',
            default => null,
        };

        // IDP hints constrain form choices only, never local authorization.
        return $type !== null ? [$type] : array_keys(self::CLINIC_ACCOUNT_TYPES);
    }

    public function hasPendingAdmissionReference(): bool
    {
        $profile = $this->relationLoaded('healthProfile') ? $this->healthProfile : (
            \Illuminate\Support\Facades\Schema::hasTable('health_profiles') ? $this->healthProfile()->first() : null
        );
        if (in_array(strtolower(trim((string) ($profile?->clearance_status ?? ''))), ['issued', 'fully cleared'], true)) {
            return false;
        }

        foreach ([$this->reference_number, $profile?->reference_number] as $reference) {
            $reference = strtoupper(trim((string) $reference));
            if ($reference !== ''
                && !preg_match('/^(CLN-|LOC-|TEST-LOCAL)/', $reference)
                && !preg_match('/^\d{4}-\d{5}-[A-Z]{2}-\d+$/', $reference)
                && $reference !== strtoupper(trim((string) $this->student_id))) {
                return true;
            }
        }

        return false;
    }

    public function clinicHealthFormAudience(): ?string
    {
        if (self::normalizeRole($this->user_role) !== self::ROLE_STUDENT) {
            return null;
        }

        if ($this->hasPendingAdmissionReference()) {
            return 'applicant';
        }

        $accountType = $this->clinicAccountTypeKey();

        if ($accountType === 'applicant') {
            $profile = $this->relationLoaded('healthProfile') ? $this->healthProfile : (
                \Illuminate\Support\Facades\Schema::hasTable('health_profiles') ? $this->healthProfile()->first() : null
            );
            if (in_array(strtolower(trim((string) ($profile?->clearance_status ?? ''))), ['issued', 'fully cleared'], true)) {
                return 'student';
            }
        }

        return match ($accountType) {
            'faculty', 'non_teaching_staff' => 'employee',
            'student' => 'student',
            'applicant' => 'applicant',
            'dependent' => 'dependent',
            default => 'unselected',
        };
    }

    public function clinicUserType(): ?string
    {
        $accountType = $this->clinicAccountTypeKey();

        return $accountType !== null ? self::userTypeForClinicAccountType($accountType) : null;
    }

    public function clinicHealthFormRoute(): ?string
    {
        return match ($this->clinicHealthFormAudience()) {
            'applicant' => 'health.form',
            'student' => 'health.form.student',
            'employee' => 'health.form.employee',
            'dependent' => 'dependent.profile.form',
            default => null,
        };
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
     'first_name',
    'last_name',
    'suffix_name',
    'name',
    'student_id',
    'student_number',
    'employee_number',
    'reference_number',
    'DOB',
    'middle_name',
    'gender',
    'height',
    'weight',
    'email',
    'contact_no',
    'course',
    'year',
    'section',
    'barcode',
    'user_role',
    'idp_role',
    'user_type',
    'status',
    'password',
    'api_pin',
    'api_pin_enabled',
    'api_pin_page_enabled',
    'api_pin_token_action_enabled',
    'api_pin_emergency_credentials_enabled',
    'api_pin_disabled',
    'is_health_profile_completed',
    'notification_read_map',
    'notification_email_enabled',
    'notification_system_enabled',
    'module_permissions',

];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'api_pin',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_admin' => 'boolean',
        'api_pin_enabled' => 'boolean',
        'api_pin_page_enabled' => 'boolean',
        'api_pin_token_action_enabled' => 'boolean',
        'api_pin_emergency_credentials_enabled' => 'boolean',
        'api_pin_disabled' => 'boolean',
        'notification_read_map' => 'array',
        'notification_email_enabled' => 'boolean',
        'notification_system_enabled' => 'boolean',
        'module_permissions' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $firstName = trim((string) ($user->first_name ?? ''));
            $middleName = trim((string) ($user->middle_name ?? ''));
            $lastName = trim((string) ($user->last_name ?? ''));
            $hasSuffixNameColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'suffix_name');
            $suffixName = $hasSuffixNameColumn ? trim((string) ($user->suffix_name ?? '')) : '';
            $name = trim((string) ($user->name ?? ''));

            if ($firstName === '' && $name !== '') {
                $parts = preg_split('/\s+/', $name) ?: [];
                $firstName = $parts[0] ?? '';
                $lastName = count($parts) > 1 ? trim(implode(' ', array_slice($parts, 1))) : '';
            }

            if ($firstName === '') {
                $firstName = 'Applicant';
            }

            if ($lastName === '') {
                $lastName = 'User';
            }

            $name = trim(implode(' ', array_filter([
                $firstName,
                $middleName,
                $lastName,
                $suffixName,
            ])));

            $user->first_name = $firstName;
            $user->middle_name = $middleName !== '' ? $middleName : null;
            $user->last_name = $lastName;
            if ($hasSuffixNameColumn) {
                $user->suffix_name = $suffixName !== '' ? $suffixName : null;
            }
            $user->name = $name;

            if (trim((string) ($user->email ?? '')) === '') {
                $seed = trim((string) ($user->student_number ?? $user->student_id ?? Str::lower(Str::random(8))));
                $user->email = Str::slug($seed, '.') . '@idp.local';
            }
        });
    }

    /**
     * MAGIC ACCESSOR for $user->name
     * Returns the structured student name when name parts are available.
     */
    public function getNameAttribute($value)
    {
        if ($this->first_name) {
            return trim(implode(' ', array_filter([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
                $this->suffix_name,
            ])));
        }
        return $value;
    }

    /**
     * RELATION: User has many appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(Admin::class, 'user_id', 'id');
    }

    public function adminHubProfile()
    {
        return $this->hasOne(AdminHub::class, 'user_id', 'id');
    }

    /**
     * SCOPE: Only students
     */
    public function scopeStudents($query)
    {
        return $query->where('is_admin', 0);
    }

    public function hasRole($roles): bool
    {
        $currentRole = self::normalizeRole($this->user_role);
        $roles = is_array($roles) ? $roles : [$roles];
        $roles = array_map(function ($role) {
            return self::normalizeRole((string) $role);
        }, $roles);

        return in_array($currentRole, $roles, true);
    }

    public function isAdminLike(): bool
    {
        return $this->hasRole(self::ROLE_SUPERADMIN);
    }

    public function isStudentAssistant(): bool
    {
        $rawRole = strtolower(trim((string) $this->user_role));
        $userType = strtolower(trim((string) ($this->user_type ?? '')));

        return in_array($rawRole, ['student_assistant', 'studentassistant', 'assistant'], true)
            || (
                self::normalizeRole($rawRole) === self::ROLE_ADMIN
                && in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)
            );
    }

    public function canAccessPermission(string $permission): bool
    {
        return app(\App\Services\ModulePermissionService::class)->can($this, $permission);
    }

    public function canAccessAnyPermission(array $permissions): bool
    {
        return app(\App\Services\ModulePermissionService::class)->canAny($this, $permissions);
    }

    public function healthProfile()
    {
        return $this->hasOne(HealthProfile::class);
    }

    public function healthProfileStaff()
    {
        return $this->hasOne(HealthProfileStaff::class);
    }

    public function employeeHealthProfile()
    {
        return $this->hasOne(EmployeeHealthProfile::class, 'user_id');
    }

    public function dependentProfile()
    {
        return $this->hasOne(DependentsProfile::class, 'user_id');
    }
}
