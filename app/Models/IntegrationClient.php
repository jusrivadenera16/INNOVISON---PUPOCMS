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
        return $this->morphMany(\Laravel\Sanctum\PersonalAccessToken::class, 'tokenable');
    }

    public function hasUsedToken(): bool
    {
        return $this->is_active
            && $this->tokens->contains(fn ($token) => $token->last_used_at !== null);
    }
}
