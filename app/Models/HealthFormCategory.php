<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthFormCategory extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function submissions()
    {
        return $this->hasMany(HealthFormSubmission::class, 'category', 'name');
    }
}
