<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];

        if (!Schema::hasColumn('users', 'profile_photo')) {
            $columns[] = 'profile_photo';
        }
        if (!Schema::hasColumn('users', 'profile_photo_updated_at')) {
            $columns[] = 'profile_photo_updated_at';
        }

        if ($columns === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($columns): void {
            if (in_array('profile_photo', $columns, true)) {
                $table->string('profile_photo')->nullable();
            }
            if (in_array('profile_photo_updated_at', $columns, true)) {
                $table->timestamp('profile_photo_updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['profile_photo', 'profile_photo_updated_at'],
            static fn (string $column): bool => Schema::hasColumn('users', $column)
        ));

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
