<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class IntegrationClient extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'system_key',
        'system_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tokens()
    {
        return $this->hasMany(\Laravel\Sanctum\PersonalAccessToken::class, 'tokenable_id')
            ->where('tokenable_type', self::class);
    }
}