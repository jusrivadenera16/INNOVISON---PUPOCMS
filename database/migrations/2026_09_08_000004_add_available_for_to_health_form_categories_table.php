<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('health_form_categories')) {
            return;
        }

        if (!Schema::hasColumn('health_form_categories', 'available_for')) {
            Schema::table('health_form_categories', function (Blueprint $table) {
                $table->json('available_for')->nullable()->after('is_active');
            });
        }

        // Preserve the existing availability of seeded categories while allowing
        // newly added categories to be limited by the creator's selections.
        $defaultAudiences = ['applicant', 'faculty', 'admin', 'dependent'];
        DB::table('health_form_categories')
            ->select(['id', 'name', 'available_for'])
            ->whereNull('available_for')
            ->orderBy('id')
            ->chunkById(100, function ($categories) use ($defaultAudiences): void {
                foreach ($categories as $category) {
                    $audiences = $defaultAudiences;
                    $normalizedName = strtolower(trim((string) $category->name));

                    if (in_array($normalizedName, ['ojt', 'on-the-job training (ojt)', 'return to school'], true)) {
                        $audiences[] = 'student';
                    }

                    DB::table('health_form_categories')
                        ->where('id', $category->id)
                        ->update(['available_for' => json_encode($audiences)]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('health_form_categories') && Schema::hasColumn('health_form_categories', 'available_for')) {
            Schema::table('health_form_categories', function (Blueprint $table) {
                $table->dropColumn('available_for');
            });
        }
    }
};
