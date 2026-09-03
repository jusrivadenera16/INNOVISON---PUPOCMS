<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'health_form_category')) {
                $table->string('health_form_category')->nullable()->after('reference_number');
            }
        });

        if (
            Schema::hasColumn('health_profiles', 'health_form_category')
            && Schema::hasTable('health_form_submissions')
        ) {
            DB::table('health_profiles')
                ->select('id')
                ->whereNull('health_form_category')
                ->orderBy('id')
                ->chunkById(100, function ($profiles): void {
                    foreach ($profiles as $profile) {
                        $category = DB::table('health_form_submissions')
                            ->where('health_profile_id', $profile->id)
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->orderByDesc('submitted_at')
                            ->orderByDesc('id')
                            ->value('category');

                        if ($category) {
                            DB::table('health_profiles')
                                ->where('id', $profile->id)
                                ->update(['health_form_category' => $category]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'health_form_category')) {
                $table->dropColumn('health_form_category');
            }
        });
    }
};
