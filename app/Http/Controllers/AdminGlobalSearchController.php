<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\EmployeeHealthProfile;
use App\Models\Item;
use App\Models\User;
use App\Services\ModulePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminGlobalSearchController extends Controller
{
    private const RESULTS_PER_GROUP = 6;

    public function search(Request $request, ModulePermissionService $permissions): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $query = trim((string) $request->query('q', ''));

        if (Str::length($query) < 2) {
            return response()->json([
                'query' => $query,
                'groups' => [],
            ]);
        }

        $groups = [];
        $this->appendGroup($groups, 'Pages', $this->pageResults($user, $permissions, $query));

        if ($permissions->can($user, 'appointments.view')) {
            $this->appendGroup($groups, 'Appointments', $this->appointmentResults($query));
        }

        if ($permissions->can($user, 'health_records.view')) {
            $this->appendGroup($groups, 'Health Records', $this->healthRecordResults($query));
        }

        if ($permissions->can($user, 'inventory.view')) {
            $this->appendGroup($groups, 'Inventory', $this->inventoryResults($query));
        }

        if ($permissions->can($user, 'announcements.view')) {
            $this->appendGroup($groups, 'Announcements', $this->announcementResults($query));
        }

        return response()->json([
            'query' => $query,
            'groups' => $groups,
        ]);
    }

    private function appendGroup(array &$groups, string $label, array $results): void
    {
        if ($results === []) {
            return;
        }

        $groups[] = [
            'label' => $label,
            'results' => $results,
        ];
    }

    private function pageResults(?User $user, ModulePermissionService $permissions, string $query): array
    {
        $pages = [
            ['label' => 'Dashboard', 'description' => 'Clinic operations overview', 'url' => route('admin.dashboard'), 'keywords' => 'home overview'],
            ['label' => 'Appointments', 'description' => 'Schedules and consultation status', 'url' => route('admin.appointments'), 'permission' => 'appointments.view'],
            ['label' => 'Walk-in', 'description' => 'Patient intake and consultation workflow', 'url' => route('walkin.index'), 'permission' => 'walkin.view'],
            ['label' => 'Health Records', 'description' => 'Health profiles and document review', 'url' => route('admin.health_records'), 'permission' => 'health_records.view'],
            ['label' => 'Inventory', 'description' => 'Medicine stock and supplies', 'url' => route('admin.inventory'), 'permission' => 'inventory.view'],
            ['label' => 'Reports', 'description' => 'Clinic reports and analytics', 'url' => route('admin.reports'), 'permission' => 'reports.view'],
            ['label' => 'Announcements', 'description' => 'Clinic bulletin management', 'url' => route('admin.announcements'), 'permission' => 'announcements.view'],
            ['label' => 'Settings', 'description' => 'Clinic and system configuration', 'url' => route('admin.settings'), 'permission' => 'settings.view'],
        ];

        if (User::normalizeRole((string) ($user?->user_role ?? '')) === User::ROLE_SUPERADMIN) {
            $pages[] = ['label' => 'Account Access', 'description' => 'Clinic roles and module permissions', 'url' => route('admin.user-management.account-access'), 'keywords' => 'users permissions'];
            $pages[] = ['label' => 'Admin Hub', 'description' => 'Admin Designee profiles and offices', 'url' => route('admin.user-management.admin-hub'), 'keywords' => 'users designee'];
            $pages[] = ['label' => 'Audit Trail', 'description' => 'Security and activity history', 'url' => route('admin.logs'), 'keywords' => 'activity logs security'];
        }

        $needle = Str::lower($query);

        return collect($pages)
            ->filter(function (array $page) use ($user, $permissions, $needle): bool {
                $permission = $page['permission'] ?? null;
                if ($permission !== null && !$permissions->can($user, $permission)) {
                    return false;
                }

                return Str::contains(Str::lower(implode(' ', [
                    $page['label'],
                    $page['description'],
                    $page['keywords'] ?? '',
                ])), $needle);
            })
            ->map(fn (array $page) => $this->result('page', $page['label'], $page['description'], $page['url']))
            ->values()
            ->all();
    }

    private function appointmentResults(string $query): array
    {
        if (!Schema::hasTable('appointments')) {
            return [];
        }

        return Appointment::query()
            ->where(function ($builder) use ($query) {
                $this->addLikeConditions($builder, 'appointments', [
                    'apt_id', 'name', 'email', 'student_id', 'student_number', 'service',
                ], $query);
            })
            ->latest('id')
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(function (Appointment $appointment) {
                $identifier = trim((string) ($appointment->apt_id ?? $appointment->student_number ?? $appointment->student_id ?? ''));
                $schedule = trim(implode(' ', array_filter([
                    (string) ($appointment->date ?? ''),
                    (string) ($appointment->time ?? ''),
                ])));
                $description = trim(implode(' - ', array_filter([
                    'Appointment',
                    $identifier,
                    $schedule,
                    (string) ($appointment->status ?? ''),
                ])));

                return $this->result(
                    'appointment',
                    (string) ($appointment->name ?: $appointment->email ?: 'Appointment'),
                    $description,
                    route('admin.appointments', ['search' => $identifier !== '' ? $identifier : $appointment->id])
                );
            })
            ->all();
    }

    private function healthRecordResults(string $query): array
    {
        $results = [];

        if (Schema::hasTable('health_profiles')) {
            $studentResults = User::query()
                ->with('healthProfile')
                ->whereHas('healthProfile')
                ->where(function ($builder) use ($query) {
                    $this->addLikeConditions($builder, 'users', [
                        'name', 'first_name', 'last_name', 'email', 'student_number', 'student_id', 'reference_number',
                    ], $query);
                })
                ->limit(self::RESULTS_PER_GROUP)
                ->get()
                ->map(function (User $user) {
                    $profile = $user->healthProfile;
                    $identifier = trim((string) ($user->student_number ?? $user->reference_number ?? $user->student_id ?? ''));
                    $description = trim(implode(' - ', array_filter([
                        'Student health record',
                        $identifier,
                        (string) ($profile?->clearance_status ?? ''),
                    ])));

                    return $this->result(
                        'health',
                        (string) ($user->name ?: $user->email ?: 'Student health record'),
                        $description,
                        route('admin.show_health', $profile->id)
                    );
                })
                ->all();

            $results = array_merge($results, $studentResults);
        }

        if (Schema::hasTable('health_profile_emp')) {
            $remaining = max(0, self::RESULTS_PER_GROUP - count($results));
            if ($remaining > 0) {
                $employeeResults = EmployeeHealthProfile::query()
                    ->where(function ($builder) use ($query) {
                        $this->addLikeConditions($builder, 'health_profile_emp', [
                            'name', 'first_name', 'last_name', 'employee_number', 'office',
                        ], $query);
                    })
                    ->latest('id')
                    ->limit($remaining)
                    ->get()
                    ->map(function (EmployeeHealthProfile $profile) {
                        $name = trim((string) ($profile->name ?? ''));
                        if ($name === '') {
                            $name = trim(implode(' ', array_filter([
                                $profile->first_name ?? '',
                                $profile->last_name ?? '',
                            ])));
                        }

                        $description = trim(implode(' - ', array_filter([
                            'Employee health record',
                            (string) ($profile->employee_number ?? ''),
                            (string) ($profile->clearance_status ?? ''),
                        ])));

                        return $this->result(
                            'health',
                            $name !== '' ? $name : 'Employee health record',
                            $description,
                            route('admin.health_records', ['user_type' => 'employee'])
                        );
                    })
                    ->all();

                $results = array_merge($results, $employeeResults);
            }
        }

        return array_slice($results, 0, self::RESULTS_PER_GROUP);
    }

    private function inventoryResults(string $query): array
    {
        if (!Schema::hasTable('items')) {
            return [];
        }

        return Item::query()
            ->where(function ($builder) use ($query) {
                $this->addLikeConditions($builder, 'items', [
                    'name', 'category', 'stock_number', 'batch_number', 'medicine_type',
                ], $query);
            })
            ->orderBy('name')
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(function (Item $item) {
                $description = trim(implode(' - ', array_filter([
                    (string) ($item->category ?? ''),
                    (string) ($item->stock_number ?? ''),
                    'Stock: ' . (string) ($item->quantity ?? 0),
                ])));

                return $this->result(
                    'inventory',
                    (string) ($item->name ?? 'Inventory item'),
                    $description,
                    route('admin.inventory', ['search' => $item->id])
                );
            })
            ->all();
    }

    private function announcementResults(string $query): array
    {
        if (!Schema::hasTable('announcements')) {
            return [];
        }

        return Announcement::query()
            ->where(function ($builder) use ($query) {
                $this->addLikeConditions($builder, 'announcements', ['title', 'message'], $query);
            })
            ->latest('id')
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(function (Announcement $announcement) {
                $description = Str::limit(trim(strip_tags((string) ($announcement->message ?? ''))), 120);

                return $this->result(
                    'announcement',
                    (string) ($announcement->title ?? 'Announcement'),
                    $description !== '' ? $description : 'Clinic announcement',
                    route('admin.announcements', ['announcement' => $announcement->id])
                );
            })
            ->all();
    }

    private function addLikeConditions($builder, string $table, array $columns, string $query): void
    {
        $availableColumns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column)));
        if ($availableColumns === []) {
            $builder->whereRaw('1 = 0');

            return;
        }

        foreach ($availableColumns as $column) {
            $builder->orWhere($table . '.' . $column, 'like', '%' . $query . '%');
        }
    }

    private function result(string $type, string $title, string $description, string $url): array
    {
        return compact('type', 'title', 'description', 'url');
    }
}
