<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PIN_COLUMNS = [
        'api_pin',
        'api_pin_enabled',
        'api_pin_disabled',
        'api_pin_page_enabled',
        'api_pin_token_action_enabled',
        'api_pin_emergency_credentials_enabled',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $columns = array_values(array_filter(
            self::PIN_COLUMNS,
            fn (string $column): bool => Schema::hasColumn('users', $column)
        ));

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'api_pin')) {
                $table->string('api_pin')->nullable();
            }
            foreach (array_diff(self::PIN_COLUMNS, ['api_pin']) as $column) {
                if (!Schema::hasColumn('users', $column)) {
                    $table->boolean($column)->default(false);
                }
            }
        });
    }
};
