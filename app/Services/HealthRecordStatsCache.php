<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB};

class HealthRecordStatsCache
{
    public function key(Request $request): ?string
    {
        try {
            $revisionQuery = null;
            foreach (['health_profiles', 'health_profile_emp', 'health_form_submissions', 'health_profile_correction_requests', 'users'] as $table) {
                // Metadata only, never decrypted medical fields or document content.
                $part = DB::table($table)->selectRaw('? as source, COUNT(*) as rows_count, MAX(updated_at) as last_update', [$table]);
                $revisionQuery = $revisionQuery ? $revisionQuery->unionAll($part) : $part;
            }
            $revision = $revisionQuery->get()->map(fn ($row) => (array) $row)->all();
            $filters = $request->only(['q', 'course', 'date', 'year', 'user_type', 'student_type', 'form_category']);
            ksort($filters);
            return 'health-record-stats:v3:' . hash('sha256', json_encode([
                config('database.default'), config('database.connections.' . config('database.default') . '.database'),
                $request->user()?->getAuthIdentifier(), $request->user()?->user_role, $filters, $revision,
            ]));
        } catch (\Throwable $exception) {
            return null; // An unavailable cache must not prevent record access.
        }
    }

    public function get(?string $key): ?array
    {
        try {
            $value = $key ? Cache::get($key) : null;
            return is_array($value) ? $value : null;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function put(?string $key, array $stats): void
    {
        try {
            // Only numeric counts are retained, scoped by actor, filters and revision.
            // Short expiry bounds same-second timestamp collisions and legacy writes.
            if ($key) Cache::put($key, $stats, 60);
        } catch (\Throwable $exception) {
            // Optional optimization; the live calculated counts remain usable.
        }
    }
}
