<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'priority',
        'target_audience',
        'show_on_landing',
        'show_in_portal',
        'message',
        'image_paths',
        'expires_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'image_paths' => 'array',
        'show_on_landing' => 'boolean',
        'show_in_portal' => 'boolean',
    ];

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->image_paths ?? [])
            ->map(fn ($path) => trim((string) $path))
            ->filter()
            ->map(fn ($path) => asset('images/' . ltrim($path, '/')))
            ->values()
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }
}
