<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmergencyAccessEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if ((bool) config('services.emergency.enabled', false)) {
            return $next($request);
        }

        return response()->view('errors.403', [], 403);
    }
}
