<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_form_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('health_form_submissions', 'profile_snapshot')) {
                $table->json('profile_snapshot')->nullable()->after('pdf_path');
            }

            if (!Schema::hasColumn('health_form_submissions', 'snapshot_captured_at')) {
                $table->timestamp('snapshot_captured_at')->nullable()->after('profile_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_form_submissions', function (Blueprint $table) {
            $columns = collect(['profile_snapshot', 'snapshot_captured_at'])
                ->filter(fn (string $column) => Schema::hasColumn('health_form_submissions', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
