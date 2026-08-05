<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ClinicWorkflowService;
use Closure;
use Illuminate\Http\Request;

class EnsureStudentAssistantWorkspaceAvailable
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('admin') ?: $request->user();
        $userType = strtolower(trim((string) ($user?->user_type ?? '')));
        $isStudentAssistant = $user
            && User::normalizeRole((string) ($user->user_role ?? '')) === User::ROLE_ADMIN
            && in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true);

        if (!$isStudentAssistant) {
            return $next($request);
        }

        $workflow = app(ClinicWorkflowService::class);
        if ($workflow->studentAssistantWorkspaceAvailable()) {
            return $next($request);
        }

        $message = 'Admin Workspace is available ' . $workflow->studentAssistantHoursLabel() . '.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->route('assistant.choose-portal')
            ->withErrors(['workspace' => $message]);
    }
}
