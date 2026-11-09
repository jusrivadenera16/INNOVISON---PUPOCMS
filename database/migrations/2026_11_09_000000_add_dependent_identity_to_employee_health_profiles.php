<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('health_profile_emp')) {
            return;
        }

        Schema::table('health_profile_emp', function (Blueprint $table): void {
            if (!Schema::hasColumn('health_profile_emp', 'id_number')) {
                $table->string('id_number', 120)->nullable();
            }
            if (!Schema::hasColumn('health_profile_emp', 'id_number_encrypted')) {
                $table->text('id_number_encrypted')->nullable();
            }
            if (!Schema::hasColumn('health_profile_emp', 'linked_employee_user_id')) {
                $table->foreignId('linked_employee_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('health_profile_emp')) {
            return;
        }

        if (Schema::hasColumn('health_profile_emp', 'linked_employee_user_id')) {
            Schema::table('health_profile_emp', function (Blueprint $table): void {
                $table->dropForeign(['linked_employee_user_id']);
            });
        }

        $columns = array_values(array_filter(
            ['id_number', 'id_number_encrypted', 'linked_employee_user_id'],
            fn (string $column): bool => Schema::hasColumn('health_profile_emp', $column)
        ));

        if ($columns !== []) {
            Schema::table('health_profile_emp', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
