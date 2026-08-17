<?php

namespace App\Http\Middleware;

use App\Services\ModulePermissionService;
use Closure;
use Illuminate\Http\Request;

class EnsureModulePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if ($permission === 'appointments.status') {
            $status = strtolower(trim((string) $request->route('status')));
            $permission = $status === 'approved' ? 'appointments.approve' : 'appointments.reject';
        }

        if (!app(ModulePermissionService::class)->can($request->user(), $permission)) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
