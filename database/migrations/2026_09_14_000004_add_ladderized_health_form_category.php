<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('health_form_categories')
            || !Schema::hasColumn('health_form_categories', 'available_for')
            || !Schema::hasColumn('health_form_categories', 'student_types')) {
            return;
        }

        $category = DB::table('health_form_categories')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['ladderized'])
            ->first();

        if (!$category) {
            DB::table('health_form_categories')->insert([
                'name' => 'Ladderized',
                'available_for' => json_encode(['student']),
                'student_types' => json_encode(['ladderized']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        $availableFor = json_decode((string) $category->available_for, true);
        $availableFor = is_array($availableFor) ? $availableFor : [];
        if (!in_array('student', $availableFor, true)) {
            $availableFor[] = 'student';
        }

        $studentTypes = json_decode((string) $category->student_types, true);
        $studentTypes = is_array($studentTypes) ? $studentTypes : [];
        if (!in_array('ladderized', $studentTypes, true)) {
            $studentTypes[] = 'ladderized';
        }

        DB::table('health_form_categories')
            ->where('id', $category->id)
            ->update([
                'available_for' => json_encode(array_values(array_unique($availableFor))),
                'student_types' => json_encode(array_values(array_unique($studentTypes))),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('health_form_categories')) {
            return;
        }

        DB::table('health_form_categories')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['ladderized'])
            ->whereJsonContains('student_types', 'ladderized')
            ->delete();
    }
};
