<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ModulePermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdminExportAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (
            $user
            && (
                User::normalizeRole((string) ($user->user_role ?? '')) === User::ROLE_SUPERADMIN
                || app(ModulePermissionService::class)->can($user, 'reports.export_reports')
            )
        ) {
            return $next($request);
        }

        $backUrl = $request->is('assistant/*')
            ? url('/assistant/reports')
            : url('/admin/reports');

        return response()->view('errors.export-reports-forbidden', compact('backUrl'), 403);
    }
}
