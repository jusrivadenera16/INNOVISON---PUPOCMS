<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'idp_user_id_encrypted',
        'id_number_encrypted',
        'first_name_encrypted',
        'middle_name_encrypted',
        'last_name_encrypted',
        'suffix_name_encrypted',
        'email_encrypted',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('dependents_profiles')) {
            return;
        }

        $columns = array_values(array_filter(
            $this->columns,
            fn (string $column) => !Schema::hasColumn('dependents_profiles', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('dependents_profiles', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->text($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('dependents_profiles')) {
            return;
        }

        $columns = array_values(array_filter(
            $this->columns,
            fn (string $column) => Schema::hasColumn('dependents_profiles', $column)
        ));

        if ($columns !== []) {
            Schema::table('dependents_profiles', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
