<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClinicServiceOption extends Model
{
    public const GROUP_REFERRAL = 'referral';
    public const GROUP_OTHER_SERVICE = 'other_service';
    public const GROUP_ONLINE_CONSULTATION = 'online_consultation';

    protected $fillable = [
        'option_group',
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeForGroup(Builder $query, string $group): Builder
    {
        return $query->where('option_group', $group);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function serviceLabel(): string
    {
        if ($this->option_group === self::GROUP_ONLINE_CONSULTATION) {
            return 'Consultation c/o ' . $this->name;
        }

        return $this->name;
    }
}
