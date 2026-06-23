<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'admin_id';

    public $timestamps = true;
    public $incrementing = true;

    protected $keyType = 'int';

    protected $guarded = [];

    private static array $columnCache = [];

    protected static function booted(): void
    {
        static::saving(function (Admin $admin) {
            $firstName = trim((string) $admin->first_name);
            $middleName = trim((string) $admin->middle_name);
            $lastName = trim((string) $admin->last_name);
            $suffixName = trim((string) $admin->suffix_name);
            $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName, $suffixName])));

            if ($fullName !== '' && static::hasColumn('name')) {
                $admin->name = $fullName;
            }
        });
    }

    public static function availableColumns(): array
    {
        $connection = DB::connection();
        $cacheKey = implode(':', [
            $connection->getName(),
            (string) $connection->getDatabaseName(),
            'admins',
        ]);

        if (!array_key_exists($cacheKey, static::$columnCache)) {
            static::$columnCache[$cacheKey] = Schema::hasTable('admins')
                ? Schema::getColumnListing('admins')
                : [];
        }

        return static::$columnCache[$cacheKey];
    }

    public static function hasColumn(string $column): bool
    {
        return in_array($column, static::availableColumns(), true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
