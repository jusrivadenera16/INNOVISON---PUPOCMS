<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['category', 'question', 'answer', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];
}
