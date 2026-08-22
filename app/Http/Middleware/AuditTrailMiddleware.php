<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $actor = Auth::user();
        $response = $next($request);

        try {
            if (!$this->shouldSkipPassiveView($request)) {
                $this->record($request, $response, $startedAt, $actor);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $response;
    }

    /**
     * Avoid recording the high-frequency feeds themselves. State-changing
     * notification actions and individual notification opens remain auditable.
     */
    private function shouldSkipPassiveView(Request $request): bool
    {
        if (!$request->isMethod('GET')) {
            return false;
        }

        $routeName = strtolower((string) optional($request->route())->getName());
        $path = '/' . ltrim(strtolower((string) $request->path()), '/');

        if (in_array($routeName, [
            'admin.notifications.feed',
            'notifications.feed',
            'admin.logs',
            'admin.global-search',
        ], true)) {
            return true;
        }

        if (in_array($path, [
            '/admin/notifications/feed',
            '/notifications/feed',
            '/admin/activity-logs',
            '/admin/global-search',
        ], true)) {
            return true;
        }

        return $path === '/student/account'
            && strtolower(trim((string) $request->query('view', ''))) === 'notifications';
    }

    private function record(Request $request, Response $response, float $startedAt, $user): void
    {
        $route = $request->route();

        if (!$user || !$route) {
            return;
        }

        $method = strtoupper((string) $request->method());
        $routeName = (string) ($route->getName() ?? '');
        $path = '/' . ltrim((string) $request->path(), '/');
        $statusCode = (int) $response->getStatusCode();
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $eventType = $this->resolveEventType($method, $statusCode);
        $module = $this->resolveModule($path, $routeName);
        [$subjectType, $subjectId] = $this->resolveSubject($request, $routeName, $path);

        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name ?? $user->email ?? 'Unknown User',
            'user_role' => strtolower((string) ($user->user_role ?? '')),
            'action' => $this->resolveAction($method, $routeName, $path),
            'module' => $module,
            'event_type' => $eventType,
            'description' => $this->resolveDescription($method, $routeName, $path, $statusCode),
            'route_name' => $routeName !== '' ? $routeName : null,
            'http_method' => $method,
            'request_path' => $path,
            'status_code' => $statusCode,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => [
                'duration_ms' => $durationMs,
                'query_keys' => array_keys($request->query()),
                'payload_keys' => $method === 'GET' ? [] : array_keys($request->except([
                    '_token',
                    '_method',
                    'password',
                    'password_confirmation',
                    'current_password',
                    'new_password',
                    'token',
                ])),
                'referer' => $request->headers->get('referer'),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    private function resolveEventType(string $method, int $statusCode): string
    {
        if ($statusCode >= 400) {
            return 'error';
        }

        return match ($method) {
            'GET', 'HEAD' => 'view',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'action',
        };
    }

    private function resolveAction(string $method, string $routeName, string $path): string
    {
        $target = $routeName !== '' ? $routeName : ltrim($path, '/');
        return $method . ' ' . $target;
    }

    private function resolveDescription(string $method, string $routeName, string $path, int $statusCode): string
    {
        $target = $routeName !== '' ? $routeName : $path;
        return "{$method} request to {$target} returned status {$statusCode}.";
    }

    private function resolveModule(string $path, string $routeName): string
    {
        $normalizedPath = ltrim(strtolower($path), '/');
        $normalizedRoute = strtolower($routeName);

        if (str_starts_with($normalizedPath, 'student/')) {
            if (str_contains($normalizedPath, 'appointments')) {
                return 'Student Appointments';
            }
            if (str_contains($normalizedPath, 'health')) {
                return 'Student Health Form';
            }
            return 'Student Portal';
        }

        if (str_starts_with($normalizedPath, 'assistant/')) {
            return 'Student Assistant Console';
        }

        if (str_starts_with($normalizedPath, 'admin/')) {
            if (str_contains($normalizedPath, 'inventory')) {
                return 'Inventory';
            }
            if (str_contains($normalizedPath, 'reports')) {
                return 'Reports';
            }
            if (str_contains($normalizedPath, 'appointments')) {
                return 'Appointments';
            }
            if (str_contains($normalizedPath, 'walkin')) {
                return 'Walk-in';
            }
            if (str_contains($normalizedPath, 'activity-logs')) {
                return 'Audit Trail';
            }
            return 'Admin Console';
        }

        if (str_contains($normalizedPath, 'health-profile') || str_contains($normalizedPath, 'health-records')) {
            return 'Health Records';
        }

        if (str_contains($normalizedRoute, 'logout') || str_contains($normalizedRoute, 'login')) {
            return 'Authentication';
        }

        return 'System';
    }

    private function resolveSubject(Request $request, string $routeName, string $path): array
    {
        $params = (array) optional($request->route())->parameters();
        $subjectId = null;

        foreach ([
            'id',
            'healthProfile',
            'employeeProfile',
            'staffProfile',
            'submission',
            'appointment',
            'student_id',
            'assistant',
            'item',
            'user',
        ] as $candidateKey) {
            if (!array_key_exists($candidateKey, $params)) {
                continue;
            }

            $value = $params[$candidateKey];
            if (is_object($value) && method_exists($value, 'getKey')) {
                $value = $value->getKey();
            }

            if (is_scalar($value) && (string) $value !== '') {
                $subjectId = (string) $value;
                break;
            }
        }

        $source = strtolower($routeName !== '' ? $routeName : $path);
        $subjectType = null;

        if (array_key_exists('submission', $params)) {
            $subjectType = 'health_form_submission';
        } elseif (array_key_exists('employeeProfile', $params) || array_key_exists('staffProfile', $params)) {
            $subjectType = 'employee_health_profile';
        } elseif (array_key_exists('healthProfile', $params)) {
            $subjectType = 'health_profile';
        } elseif (str_contains($source, 'appointment')) {
            $subjectType = 'appointment';
        } elseif (str_contains($source, 'inventory') || str_contains($source, 'item')) {
            $subjectType = 'inventory_item';
        } elseif (str_contains($source, 'health-profile') || str_contains($source, 'health') || str_contains($source, 'clearance')) {
            $subjectType = 'health_profile';
        } elseif (str_contains($source, 'walkin')) {
            $subjectType = 'walkin';
        } elseif (str_contains($source, 'report')) {
            $subjectType = 'report';
        } elseif (str_contains($source, 'student-assistant')) {
            $subjectType = 'admin';
        }

        return [$subjectType, $subjectId];
    }
}
