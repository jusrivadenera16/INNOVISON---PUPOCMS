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

    /**
     * The attributes that are mass assignable.
     */
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
     'first_name',
    'last_name',
    'name',
    'student_id',
    'student_number',
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
    'api_pin_disabled',
    'is_health_profile_completed',
    'notification_read_map',

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
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'api_pin_enabled' => 'boolean',
        'api_pin_page_enabled' => 'boolean',
        'api_pin_token_action_enabled' => 'boolean',
        'api_pin_disabled' => 'boolean',
        'notification_read_map' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $firstName = trim((string) ($user->first_name ?? ''));
            $middleName = trim((string) ($user->middle_name ?? ''));
            $lastName = trim((string) ($user->last_name ?? ''));
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
            ])));

            $user->first_name = $firstName;
            $user->middle_name = $middleName !== '' ? $middleName : null;
            $user->last_name = $lastName;
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

    public function healthProfile()
    {
        return $this->hasOne(HealthProfile::class);
    }
}
