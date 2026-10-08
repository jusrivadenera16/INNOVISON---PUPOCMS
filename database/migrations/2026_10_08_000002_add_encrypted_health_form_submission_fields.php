<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FIELD_MAP = [
        'profile_snapshot' => 'profile_snapshot_encrypted',
        'remarks' => 'remarks_encrypted',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('health_form_submissions')) {
            return;
        }

        $columns = array_values(array_filter(
            self::FIELD_MAP,
            static fn (string $encryptedColumn, string $sourceColumn): bool => Schema::hasColumn('health_form_submissions', $sourceColumn)
                && !Schema::hasColumn('health_form_submissions', $encryptedColumn),
            ARRAY_FILTER_USE_BOTH
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('health_form_submissions', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->text($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('health_form_submissions')) {
            return;
        }

        $columns = array_values(array_filter(
            array_values(self::FIELD_MAP),
            static fn (string $column): bool => Schema::hasColumn('health_form_submissions', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('health_form_submissions', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
