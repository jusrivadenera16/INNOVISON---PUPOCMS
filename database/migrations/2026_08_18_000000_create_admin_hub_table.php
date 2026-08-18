<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_hub')) {
            Schema::create('admin_hub', function (Blueprint $table) {
                $table->id();
                $table->string('admin_uuid')->nullable()->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('first_name')->nullable();
                $table->string('middle_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('suffix_name')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->nullable()->index();
                $table->string('office')->nullable();
                $table->string('role', 50)->default('admin_designee');
                $table->string('status', 30)->default('active');
                $table->timestamps();
            });
        }

        $this->copyExistingAdminDesignees();
    }

    private function copyExistingAdminDesignees(): void
    {
        if (!Schema::hasTable('admins') || !Schema::hasTable('admin_hub')) {
            return;
        }

        $adminColumns = Schema::getColumnListing('admins');
        $has = fn (string $column): bool => in_array($column, $adminColumns, true);

        if (!$has('admin_hub_role') && !$has('access_level')) {
            return;
        }

        $query = DB::table('admins');
        $query->where(function ($builder) use ($has) {
            if ($has('admin_hub_role')) {
                $builder->orWhereIn('admin_hub_role', ['admin_designee', 'designee']);
            }

            if ($has('access_level')) {
                $builder->orWhere('access_level', 'designee');
            }
        });

        if ($has('admin_id')) {
            $query->orderBy('admin_id');
        }

        $query->get()->each(function ($admin) use ($has) {
            $email = trim((string) ($admin->email ?? ($admin->email_address ?? '')));
            $adminUuid = $has('external_identifier')
                ? trim((string) ($admin->external_identifier ?? ''))
                : '';

            $payload = [
                'admin_uuid' => $adminUuid !== '' ? $adminUuid : null,
                'user_id' => $has('user_id') ? ($admin->user_id ?? null) : null,
                'first_name' => $admin->first_name ?? null,
                'middle_name' => $has('middle_name') ? ($admin->middle_name ?? null) : null,
                'last_name' => $admin->last_name ?? null,
                'suffix_name' => $has('suffix_name') ? ($admin->suffix_name ?? null) : null,
                'name' => $admin->name ?? null,
                'email' => $email !== '' ? $email : null,
                'office' => $admin->office ?? null,
                'role' => 'admin_designee',
                'status' => $has('status') && trim((string) ($admin->status ?? '')) !== ''
                    ? (string) $admin->status
                    : 'active',
                'created_at' => $admin->created_at ?? now(),
                'updated_at' => $admin->updated_at ?? now(),
            ];

            if ($adminUuid !== '') {
                DB::table('admin_hub')->updateOrInsert(['admin_uuid' => $adminUuid], $payload);
                return;
            }

            if ($email !== '') {
                DB::table('admin_hub')->updateOrInsert(['email' => $email], $payload);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_hub');
    }
};
