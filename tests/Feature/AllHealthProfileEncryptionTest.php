<?php

namespace Tests\Feature;

use App\Models\DependentsProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
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
        $this->assertStringNotContainsString('private@example.test', (string) $submissionRaw->profile_snapshot_encrypted);
        $this->assertStringNotContainsString('medical follow-up', (string) $submissionRaw->remarks_encrypted);

        $freshStudent = HealthProfile::withoutGlobalScopes()->findOrFail($student->id);
        $freshEmployee = EmployeeHealthProfile::withoutGlobalScopes()->findOrFail($employee->id);
        $freshDependent = DependentsProfile::withoutGlobalScopes()->findOrFail($dependent->id);
        $freshSubmission = HealthFormSubmission::withoutGlobalScopes()->findOrFail($submission->id);

        $this->assertSame('123 Private Street', $freshStudent->home_address);
        $this->assertSame(['Asthma'], $freshStudent->medical_history);
        $this->assertSame('Juan Dela Cruz', $freshEmployee->emergency_contact_person);
        $this->assertSame(['Hypertension'], $freshEmployee->past_medical_history);
        $this->assertSame('456 Private Avenue', $freshDependent->home_address);
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
}
