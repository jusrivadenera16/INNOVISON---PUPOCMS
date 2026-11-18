<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateUserPermissions(function (array $permissions): array {
            $normalized = array_map(fn ($permission) => strtolower(trim((string) $permission)), $permissions);

            if (in_array('inventory.manage', $normalized, true)
                && !in_array('inventory.issue_stock', $normalized, true)) {
                $permissions[] = 'inventory.issue_stock';
            }

            return array_values(array_unique($permissions));
        });
    }

    public function down(): void
    {
        $this->updateUserPermissions(fn (array $permissions): array => array_values(array_filter(
            $permissions,
            fn ($permission) => strtolower(trim((string) $permission)) !== 'inventory.issue_stock'
        )));
    }

    private function updateUserPermissions(callable $transform): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'module_permissions')) {
            return;
        }

        DB::table('users')
            ->select(['id', 'module_permissions'])
            ->whereNotNull('module_permissions')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($transform): void {
                foreach ($users as $user) {
                    $permissions = json_decode((string) $user->module_permissions, true);

                    if (!is_array($permissions)) {
                        continue;
                    }

                    $updated = $transform($permissions);
                    if ($updated === $permissions) {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['module_permissions' => json_encode($updated)]);
                }
            });
    }
};
