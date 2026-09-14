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

        if (!Schema::hasColumn('health_form_categories', 'student_types')) {
            Schema::table('health_form_categories', function (Blueprint $table) {
                $table->json('student_types')->nullable()->after('available_for');
            });
        }

        $legacyStudentTypeNames = [
            'ojt' => ['ojt', 'on the job training', 'on-the-job training', 'on-the-job training (ojt)'],
            'transferee' => ['transfer student', 'transfer students', 'transferee'],
            'returnee' => ['return to school', 'returning student', 'returning students', 'returnee'],
        ];

        DB::table('health_form_categories')
            ->select(['id', 'name', 'available_for', 'student_types'])
            ->orderBy('id')
            ->chunkById(100, function ($categories) use ($legacyStudentTypeNames): void {
                foreach ($categories as $category) {
                    if (!empty($category->student_types)) {
                        continue;
                    }

                    $availableFor = json_decode((string) $category->available_for, true);
                    if (!is_array($availableFor) || !in_array('student', $availableFor, true)) {
                        continue;
                    }

                    $normalizedName = strtolower(trim((string) $category->name));
                    $studentType = null;
                    foreach ($legacyStudentTypeNames as $type => $names) {
                        if (in_array($normalizedName, $names, true)) {
                            $studentType = $type;
                            break;
                        }
                    }

                    if ($studentType !== null) {
                        DB::table('health_form_categories')
                            ->where('id', $category->id)
                            ->update(['student_types' => json_encode([$studentType])]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('health_form_categories') && Schema::hasColumn('health_form_categories', 'student_types')) {
            Schema::table('health_form_categories', function (Blueprint $table) {
                $table->dropColumn('student_types');
            });
        }
    }
};
