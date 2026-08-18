<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminHub extends Model
{
    protected $table = 'admin_hub';

    protected $guarded = [];

    private static array $columnCache = [];

    protected static function booted(): void
    {
        static::saving(function (AdminHub $adminHub) {
            $firstName = trim((string) $adminHub->first_name);
            $middleName = trim((string) $adminHub->middle_name);
            $lastName = trim((string) $adminHub->last_name);
            $suffixName = trim((string) $adminHub->suffix_name);
            $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName, $suffixName])));

            if ($fullName !== '' && static::hasColumn('name')) {
                $adminHub->name = $fullName;
            }
        });
    }

    public static function availableColumns(): array
    {
        $connection = DB::connection();
        $cacheKey = implode(':', [
            $connection->getName(),
            (string) $connection->getDatabaseName(),
            'admin_hub',
        ]);

        if (!array_key_exists($cacheKey, static::$columnCache)) {
            static::$columnCache[$cacheKey] = Schema::hasTable('admin_hub')
                ? Schema::getColumnListing('admin_hub')
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
