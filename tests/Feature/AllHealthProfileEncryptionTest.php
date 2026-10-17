<?php

namespace Tests\Feature;

use App\Models\DependentsProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AllHealthProfileEncryptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('health_data_encryption.enabled', true);
        Config::set('health_data_encryption.key', 'base64:' . base64_encode(str_repeat('h', 32)));
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('home_address')->nullable();
            $table->json('medical_history')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profile_emp', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('emergency_contact_person')->nullable();
            $table->json('past_medical_history')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dependents_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('idp_user_id')->nullable();
            $table->string('id_number')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('email')->nullable();
            $table->date('birthday')->nullable();
            $table->string('home_address')->nullable();
            $table->timestamps();
        });

        Schema::create('health_form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('profile_snapshot')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        $migration = require base_path('database/migrations/2026_10_08_000001_add_encrypted_health_profile_fields.php');
        $migration->up();
        $dependentIdentityMigration = require base_path('database/migrations/2026_10_17_000002_add_encrypted_dependent_identity_fields.php');
        $dependentIdentityMigration->up();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('dependents_profiles');
        Schema::dropIfExists('health_profile_emp');
        Schema::dropIfExists('health_profiles');
        Schema::dropIfExists('health_form_submissions');

        parent::tearDown();
    }

    public function test_student_employee_and_dependent_fields_round_trip_through_encrypted_mirrors(): void
    {
        $student = HealthProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'home_address' => '123 Private Street',
            'medical_history' => ['Asthma'],
        ]);
        $employee = EmployeeHealthProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'emergency_contact_person' => 'Juan Dela Cruz',
            'past_medical_history' => ['Hypertension'],
        ]);
        $dependent = DependentsProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'idp_user_id' => 'dependent-id-123',
            'id_number' => 'D-123456',
            'first_name' => 'Private',
            'middle_name' => 'Middle',
            'last_name' => 'Identity',
            'suffix_name' => 'Jr.',
            'email' => 'private-dependent@example.test',
            'birthday' => '2000-01-01',
            'home_address' => '456 Private Avenue',
        ]);
        $submission = HealthFormSubmission::withoutGlobalScopes()->create([
            'user_id' => null,
            'profile_snapshot' => [
                'profile' => ['medical_history' => ['Asthma']],
                'user' => ['email' => 'private@example.test'],
            ],
            'remarks' => 'Requires medical follow-up.',
        ]);

        $studentRaw = DB::table('health_profiles')->where('id', $student->id)->first();
        $employeeRaw = DB::table('health_profile_emp')->where('id', $employee->id)->first();
        $dependentRaw = DB::table('dependents_profiles')->where('id', $dependent->id)->first();
        $submissionRaw = DB::table('health_form_submissions')->where('id', $submission->id)->first();

        $this->assertStringNotContainsString('Private Street', (string) $studentRaw->home_address_encrypted);
        $this->assertStringNotContainsString('Asthma', (string) $studentRaw->medical_history_encrypted);
        $this->assertStringNotContainsString('Juan Dela Cruz', (string) $employeeRaw->emergency_contact_person_encrypted);
        $this->assertStringNotContainsString('Hypertension', (string) $employeeRaw->past_medical_history_encrypted);
        $this->assertStringNotContainsString('Private Avenue', (string) $dependentRaw->home_address_encrypted);
        $this->assertStringNotContainsString('D-123456', (string) $dependentRaw->id_number_encrypted);
        $this->assertStringNotContainsString('Private', (string) $dependentRaw->first_name_encrypted);
        $this->assertStringNotContainsString('private-dependent@example.test', (string) $dependentRaw->email_encrypted);
        $this->assertStringNotContainsString('private@example.test', (string) $submissionRaw->profile_snapshot_encrypted);
        $this->assertStringNotContainsString('medical follow-up', (string) $submissionRaw->remarks_encrypted);
        $this->assertNull($studentRaw->home_address);
        $this->assertNull($studentRaw->medical_history);
        $this->assertNull($employeeRaw->emergency_contact_person);
        $this->assertNull($employeeRaw->past_medical_history);
        $this->assertNull($dependentRaw->birthday);
        $this->assertNull($dependentRaw->home_address);
        $this->assertNull($dependentRaw->id_number);
        $this->assertNull($dependentRaw->first_name);
        $this->assertNull($dependentRaw->email);
        $this->assertNull($submissionRaw->profile_snapshot);
        $this->assertNull($submissionRaw->remarks);

        $freshStudent = HealthProfile::withoutGlobalScopes()->findOrFail($student->id);
        $freshEmployee = EmployeeHealthProfile::withoutGlobalScopes()->findOrFail($employee->id);
        $freshDependent = DependentsProfile::withoutGlobalScopes()->findOrFail($dependent->id);
        $freshSubmission = HealthFormSubmission::withoutGlobalScopes()->findOrFail($submission->id);

        $this->assertSame('123 Private Street', $freshStudent->home_address);
        $this->assertSame(['Asthma'], $freshStudent->medical_history);
        $this->assertSame('Juan Dela Cruz', $freshEmployee->emergency_contact_person);
        $this->assertSame(['Hypertension'], $freshEmployee->past_medical_history);
        $this->assertSame('456 Private Avenue', $freshDependent->home_address);
        $this->assertSame('D-123456', $freshDependent->id_number);
        $this->assertSame('Private', $freshDependent->first_name);
        $this->assertSame('Middle', $freshDependent->middle_name);
        $this->assertSame('Identity', $freshDependent->last_name);
        $this->assertSame('Jr.', $freshDependent->suffix_name);
        $this->assertSame('private-dependent@example.test', $freshDependent->email);
        $this->assertSame('2000-01-01', $freshDependent->birthday?->format('Y-m-d'));
        $this->assertSame(['Asthma'], $freshSubmission->snapshotProfile()['medical_history']);
        $this->assertSame('Requires medical follow-up.', $freshSubmission->remarks);
        $this->assertSame('123 Private Street', $freshStudent->toArray()['home_address']);
        $this->assertSame(['Hypertension'], $freshEmployee->toArray()['past_medical_history']);
        $this->assertSame('456 Private Avenue', $freshDependent->toArray()['home_address']);
        $this->assertSame('Requires medical follow-up.', $freshSubmission->toArray()['remarks']);

        DB::table('health_form_submissions')
            ->where('id', $submission->id)
            ->update(['profile_snapshot' => null]);

        $encryptedOnlySubmission = HealthFormSubmission::withoutGlobalScopes()->findOrFail($submission->id);
        $this->assertSame(['medical_history' => ['Asthma']], $encryptedOnlySubmission->snapshotProfile());
    }

    public function test_app_key_ciphertext_is_not_accepted_as_health_data_ciphertext(): void
    {
        $payload = Crypt::encryptString('legacy health data');

        $this->expectException(\Illuminate\Contracts\Encryption\DecryptException::class);

        app(\App\Services\HealthDataEncrypter::class)->decrypt($payload);
    }
}
