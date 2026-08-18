<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminHub;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AdminProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!Schema::hasTable('admin_hub')) {
            return $this->errorResponse('Admin Hub table was not found.', 404);
        }

        $query = AdminHub::query();
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['admin_uuid', 'name', 'first_name', 'middle_name', 'last_name', 'suffix_name', 'email', 'office', 'role', 'status'] as $column) {
                    if (!AdminHub::hasColumn($column)) {
                        continue;
                    }

                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        foreach (['admin_uuid', 'email', 'role', 'status'] as $column) {
            $value = trim((string) $request->query($column, ''));
            if ($value !== '' && AdminHub::hasColumn($column)) {
                $query->where($column, $value);
            }
        }

        $limit = max(1, min((int) $request->query('limit', 25), 100));
        $admins = $query->orderBy($this->defaultAdminHubOrderColumn())
            ->limit($limit)
            ->get()
            ->map(fn (AdminHub $admin) => $this->transformAdminHub($admin))
            ->values()
            ->all();

        return $this->successResponse($admins, 'Admin profiles retrieved successfully.');
    }

    public function options(Request $request): JsonResponse
    {
        if (!Schema::hasTable('admin_hub')) {
            return $this->errorResponse('Admin Hub table was not found.', 404);
        }

        $query = AdminHub::query();
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['admin_uuid', 'first_name', 'last_name', 'suffix_name', 'email'] as $column) {
                    if (!AdminHub::hasColumn($column)) {
                        continue;
                    }

                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $limit = max(1, min((int) $request->query('limit', 100), 250));
        $admins = $query->orderBy($this->defaultAdminHubOrderColumn())
            ->limit($limit)
            ->get()
            ->map(fn (AdminHub $admin) => $this->transformAdminHubOption($admin))
            ->values()
            ->all();

        return $this->successResponse($admins, 'Administrator options retrieved successfully.');
    }

    public function lookup(Request $request): JsonResponse
    {
        if (!Schema::hasTable('admin_hub')) {
            return $this->errorResponse('Admin Hub table was not found.', 404);
        }

        $lookup = $this->resolveAdminHubLookup($request);
        if ($lookup === null) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        [$column, $value] = $lookup;
        if (!AdminHub::hasColumn($column)) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        $admin = AdminHub::query()->where($column, $value)->first();
        if (!$admin) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        return $this->successResponse($this->transformAdminHub($admin), 'Admin profile retrieved successfully.');
    }

    public function show($admin_id): JsonResponse
    {
        if (!Schema::hasTable('admins')) {
            return $this->errorResponse('Admins table was not found.', 404);
        }

        if (!Admin::hasColumn('admin_id')) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        $admin = Admin::query()->where('admin_id', $admin_id)->first();

        if (!$admin) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        return $this->successResponse($this->transformAdmin($admin), 'Admin profile retrieved successfully.');
    }

    public function externalShow($admin_uuid): JsonResponse
    {
        if (!Schema::hasTable('admin_hub')) {
            return $this->errorResponse('Admin Hub table was not found.', 404);
        }

        if (!AdminHub::hasColumn('admin_uuid')) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        $admin = AdminHub::query()->where('admin_uuid', $admin_uuid)->first();

        if (!$admin) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        return $this->successResponse($this->transformAdminHub($admin), 'Admin profile retrieved successfully.');
    }

    public function externalUpdate(Request $request, $admin_uuid): JsonResponse
    {
        if (!Schema::hasTable('admin_hub')) {
            return $this->errorResponse('Admin Hub table was not found.', 404);
        }

        if (!AdminHub::hasColumn('admin_uuid')) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        $admin = AdminHub::query()->where('admin_uuid', $admin_uuid)->first();

        if (!$admin) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'suffix_name' => 'nullable|string|max:50',
            'office' => 'nullable|string|max:255',
            'role' => 'nullable|in:admin_designee,designee',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors(),
                ],
                'message' => 'Validation failed.',
            ], 422);
        }

        $validated = $validator->validated();
        if (array_key_exists('role', $validated)) {
            $validated['role'] = 'admin_designee';
        }

        $admin->fill($this->filterSupportedAdminHubColumns($validated));
        $admin->save();

        return $this->successResponse($this->transformAdminHub($admin->fresh()), 'Admin profile updated successfully.');
    }

    private function resolveLookup(Request $request): ?array
    {
        foreach (['admin_id', 'email', 'email_address', 'contact_no', 'emergency_contact_no'] as $column) {
            $value = trim((string) $request->query($column, ''));
            if ($value !== '') {
                return [$column, $value];
            }
        }

        return null;
    }

    private function resolveAdminHubLookup(Request $request): ?array
    {
        foreach (['admin_uuid', 'email'] as $column) {
            $value = trim((string) $request->query($column, ''));
            if ($value !== '') {
                return [$column, $value];
            }
        }

        $legacyAdminId = trim((string) $request->query('admin_id', ''));
        if ($legacyAdminId !== '') {
            return ['admin_uuid', $legacyAdminId];
        }

        return null;
    }

    public function update(Request $request): JsonResponse
    {
        if (!Schema::hasTable('admins')) {
            return $this->errorResponse('Admins table was not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,admin_id',
            'email' => 'nullable|email',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'suffix_name' => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string|max:255',
            'civil_status' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'emergency_contact_person' => 'nullable|string|max:255',
            'emergency_contact_no' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'access_level' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors(),
                ],
                'message' => 'Validation failed.',
            ], 422);
        }

        $validated = $validator->validated();

        $admin = Admin::query()->where('admin_id', $validated['admin_id'])->first();
        if (!$admin) {
            return $this->errorResponse('Admin profile not found.', 404);
        }

        $payload = collect($validated)
            ->except('admin_id')
            ->all();

        if (
            array_key_exists('contact_no', $payload) &&
            !Admin::hasColumn('contact_no') &&
            Admin::hasColumn('emergency_contact_no')
        ) {
            $payload['emergency_contact_no'] = $payload['contact_no'];
            unset($payload['contact_no']);
        }

        $admin->fill($this->filterSupportedColumns($payload));
        $admin->save();

        return $this->successResponse($this->transformAdmin($admin->fresh()), 'Admin profile updated successfully.');
    }

    private function transformAdmin(Admin $admin): array
    {
        $data = [];

        foreach ($this->responseFieldMap() as $outputField => $candidateColumns) {
            $value = $this->pickFirstAvailableValue($admin, $candidateColumns);
            if ($value !== null && $value !== '') {
                $data[$outputField] = $value;
            }
        }

        if (!isset($data['name'])) {
            $name = trim(implode(' ', array_filter([
                $this->pickFirstAvailableValue($admin, ['first_name']),
                $this->pickFirstAvailableValue($admin, ['middle_name']),
                $this->pickFirstAvailableValue($admin, ['last_name']),
                $this->pickFirstAvailableValue($admin, ['suffix_name']),
            ])));

            if ($name !== '') {
                $data['name'] = $name;
            }
        }

        return $data;
    }

    private function transformAdminOption(Admin $admin): array
    {
        return [
            'id' => $this->pickFirstAvailableValue($admin, ['admin_id', 'id']),
            'first_name' => $this->pickFirstAvailableValue($admin, ['first_name']) ?? '',
            'last_name' => $this->pickFirstAvailableValue($admin, ['last_name']) ?? '',
            'suffix_name' => $this->pickFirstAvailableValue($admin, ['suffix_name']),
            'email' => $this->pickFirstAvailableValue($admin, ['email', 'email_address']) ?? '',
            'status' => $this->pickFirstAvailableValue($admin, ['status', 'is_active']) ?? 'N/A',
        ];
    }

    private function transformAdminHub(AdminHub $admin): array
    {
        $data = [];

        foreach ($this->adminHubResponseFieldMap() as $outputField => $candidateColumns) {
            $value = $this->pickFirstAvailableAdminHubValue($admin, $candidateColumns);
            if ($value !== null && $value !== '') {
                $data[$outputField] = $value;
            }
        }

        if (!isset($data['name'])) {
            $name = trim(implode(' ', array_filter([
                $this->pickFirstAvailableAdminHubValue($admin, ['first_name']),
                $this->pickFirstAvailableAdminHubValue($admin, ['middle_name']),
                $this->pickFirstAvailableAdminHubValue($admin, ['last_name']),
                $this->pickFirstAvailableAdminHubValue($admin, ['suffix_name']),
            ])));

            if ($name !== '') {
                $data['name'] = $name;
            }
        }

        return $data;
    }

    private function transformAdminHubOption(AdminHub $admin): array
    {
        return [
            'id' => $this->pickFirstAvailableAdminHubValue($admin, ['admin_uuid']),
            'admin_uuid' => $this->pickFirstAvailableAdminHubValue($admin, ['admin_uuid']),
            'first_name' => $this->pickFirstAvailableAdminHubValue($admin, ['first_name']) ?? '',
            'last_name' => $this->pickFirstAvailableAdminHubValue($admin, ['last_name']) ?? '',
            'suffix_name' => $this->pickFirstAvailableAdminHubValue($admin, ['suffix_name']),
            'email' => $this->pickFirstAvailableAdminHubValue($admin, ['email']) ?? '',
            'status' => $this->pickFirstAvailableAdminHubValue($admin, ['status']) ?? 'N/A',
        ];
    }

    private function responseFieldMap(): array
    {
        return [
            'admin_id' => ['admin_id', 'id'],
            'first_name' => ['first_name'],
            'middle_name' => ['middle_name'],
            'last_name' => ['last_name'],
            'suffix_name' => ['suffix_name'],
            'name' => ['name', 'full_name'],
            'email' => ['email', 'email_address'],
            'office' => ['office', 'offices'],
            'address' => ['address'],
            'contact_no' => ['contact_no', 'emergency_contact_no'],
            'emergency_contact_person' => ['emergency_contact_person'],
            'emergency_contact_no' => ['emergency_contact_no'],
            'age' => ['age'],
            'gender' => ['gender'],
            'birthday' => ['birthday'],
            'civil_status' => ['civil_status'],
            'access_level' => ['access_level', 'role', 'user_role', 'admin_role'],
            'status' => ['status'],
            'is_active' => ['is_active'],
        ];
    }

    private function adminHubResponseFieldMap(): array
    {
        return [
            'admin_uuid' => ['admin_uuid'],
            'first_name' => ['first_name'],
            'middle_name' => ['middle_name'],
            'last_name' => ['last_name'],
            'suffix_name' => ['suffix_name'],
            'name' => ['name'],
            'email' => ['email'],
            'office' => ['office'],
            'role' => ['role'],
            'status' => ['status'],
        ];
    }

    private function pickFirstAvailableValue(Admin $admin, array $candidateColumns)
    {
        foreach ($candidateColumns as $column) {
            if (Admin::hasColumn($column)) {
                return $admin->getAttribute($column);
            }
        }

        return null;
    }

    private function pickFirstAvailableAdminHubValue(AdminHub $admin, array $candidateColumns)
    {
        foreach ($candidateColumns as $column) {
            if (AdminHub::hasColumn($column)) {
                return $admin->getAttribute($column);
            }
        }

        return null;
    }

    private function filterSupportedColumns(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $column => $value) {
            if (Admin::hasColumn($column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function filterSupportedAdminHubColumns(array $attributes): array
    {
        $filtered = [];

        foreach ($attributes as $column => $value) {
            if (AdminHub::hasColumn($column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function defaultOrderColumn(): string
    {
        foreach (['name', 'first_name', 'admin_id', 'id'] as $column) {
            if (Admin::hasColumn($column)) {
                return $column;
            }
        }

        return 'admin_id';
    }

    private function defaultAdminHubOrderColumn(): string
    {
        foreach (['name', 'first_name', 'admin_uuid', 'id'] as $column) {
            if (AdminHub::hasColumn($column)) {
                return $column;
            }
        }

        return 'id';
    }

    private function successResponse($data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
        ], $status);
    }
}
