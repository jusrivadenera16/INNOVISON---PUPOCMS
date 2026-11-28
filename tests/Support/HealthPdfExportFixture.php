<?php

namespace Tests\Support;

use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HealthPdfExportFixture
{
    public static function schema(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'health_data_encryption.enabled' => false, 'cache.default' => 'array', 'session.driver' => 'array',
            'health_files.encryption_enabled' => true, 'health_files.encryption_key' => base64_encode(str_repeat('x', 32))]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'course', 'gender', 'year', 'section', 'contact_no',
                'student_number', 'reference_number', 'employee_number', 'user_type', 'user_role', 'idp_role', 'status', 'height', 'weight'] as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            foreach (['report_name', 'report_position', 'report_credentials'] as $field) $table->string($field)->nullable();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            foreach (['reference_number', 'student_number', 'employee_number', 'clearance_status', 'course_college', 'school_year', 'sex',
                'home_address', 'guardian_name', 'cellphone', 'landline', 'covid_vaccinated', 'has_disability', 'has_illness', 'disability_type',
                'other_illness', 'food_allergies', 'other_med_allergies', 'physical_assessment_status', 'medical_condition_remarks',
                'assessment_remarks', 'med_assessment_remarks', 'pending_reason', 'encode_remarks', 'med_cert_findings', 'xray_findings',
                'med_cert_findings_details', 'xray_findings_details', 'height', 'weight'] as $field) $table->text($field)->nullable();
            foreach (['vaccine_history', 'medical_history', 'medicine_allergies'] as $field) $table->json($field)->nullable();
            $table->timestamp('verified_at')->nullable(); $table->timestamps();
        });
        (require database_path('migrations/2026_11_28_000001_create_health_pdf_exports.php'))->up();
        // Isolate each test/benchmark process: parallel suites must not erase another run's parts.
        Storage::set('health_private', Storage::fake('health_pdf_export_'.\Illuminate\Support\Str::uuid()));
    }

    public static function owner(): User
    {
        $owner = User::forceCreate(['name' => 'Synthetic Export Nurse', 'user_role' => 'superadmin', 'user_type' => 'Admin', 'status' => 'active']);
        DB::table('admins')->insert(['user_id' => $owner->id, 'report_position' => 'Nurse', 'report_credentials' => 'RN']);
        return $owner;
    }

    public static function records(int $count): void
    {
        // Entirely invented data; never use production or patient data in benchmarks.
        for ($i = $count; $i > 0; $i--) {
            $user = ['name' => 'Synthetic Patient '.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'first_name' => 'Synthetic', 'last_name' => 'Patient '.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'user_type' => 'Student', 'student_number' => '2026-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT).'-TG-0',
                'course' => 'BSIT', 'gender' => 'Female', 'year' => '3', 'section' => '1', 'contact_no' => '09000000000'];
            $userId = DB::table('users')->insertGetId($user);
            DB::table('health_profiles')->insert(['user_id' => $userId, 'student_number' => $user['student_number'], 'course_college' => 'BSIT',
                'school_year' => '2026-2027', 'clearance_status' => 'Fully Cleared', 'sex' => 'Female',
                'home_address' => 'Synthetic Street, Sample Barangay, Sample City', 'guardian_name' => 'Synthetic Guardian',
                'cellphone' => '09000000001', 'covid_vaccinated' => 'Yes', 'has_illness' => 'Yes', 'medical_history' => json_encode(['Asthma']),
                'vaccine_history' => json_encode(['first_dose' => ['brand' => 'Sample', 'date' => '2021-11-15'], 'second_dose' => ['brand' => 'Sample', 'date' => '2021-12-06']]),
                'height' => '160', 'weight' => '60', 'verified_at' => '2026-01-15 10:00:00', 'created_at' => '2026-01-15 10:00:00']);
        }
    }
}
