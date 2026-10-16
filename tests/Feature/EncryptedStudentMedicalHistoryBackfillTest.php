<?php

namespace Tests\Feature;

use App\Models\HealthProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EncryptedStudentMedicalHistoryBackfillTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('health_data_encryption.enabled', false);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->json('medical_history')->nullable();
            $table->text('other_illness')->nullable();
            $table->string('food_allergies')->nullable();
            $table->json('medicine_allergies')->nullable();
            $table->string('other_med_allergies')->nullable();
            $table->json('vaccine_history')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('health_profiles');

        parent::tearDown();
    }

    public function test_migration_adds_and_removes_encrypted_mirror_columns(): void
    {
        $migration = require base_path('database/migrations/2026_10_05_000000_add_encrypted_student_medical_history_columns.php');
        $migration->up();

        foreach ($this->encryptedColumns() as $column) {
            $this->assertTrue(Schema::hasColumn('health_profiles', $column));
        }

        $migration->down();

        foreach ($this->encryptedColumns() as $column) {
            $this->assertFalse(Schema::hasColumn('health_profiles', $column));
        }
    }

    public function test_backfill_dry_run_does_not_write_and_apply_writes_ciphertext_mirrors(): void
    {
        $migration = require base_path('database/migrations/2026_10_05_000000_add_encrypted_student_medical_history_columns.php');
        $migration->up();

        $profile = HealthProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'medical_history' => ['Asthma'],
            'other_illness' => 'None',
            'food_allergies' => 'Peanuts',
            'medicine_allergies' => ['Aspirin'],
            'other_med_allergies' => 'None',
            'vaccine_history' => ['brand' => 'Example'],
        ]);

        $this->assertSame(0, Artisan::call('health:encrypt-student-medical-history'));
        $this->assertNull(DB::table('health_profiles')->where('id', $profile->id)->value('medical_history_encrypted'));

        $this->assertSame(0, Artisan::call('health:encrypt-student-medical-history', ['--apply' => true]));

        $raw = DB::table('health_profiles')->where('id', $profile->id)->first();
        $this->assertNotSame('Asthma', $raw->medical_history_encrypted);
        $this->assertNotSame('Peanuts', $raw->food_allergies_encrypted);

        $fresh = HealthProfile::withoutGlobalScopes()->findOrFail($profile->id);
        $this->assertSame(['Asthma'], $fresh->medical_history_encrypted);
        $this->assertSame('None', $fresh->other_illness_encrypted);
        $this->assertSame('Peanuts', $fresh->food_allergies_encrypted);
        $this->assertSame(['Aspirin'], $fresh->medicine_allergies_encrypted);
        $this->assertSame(['brand' => 'Example'], $fresh->vaccine_history_encrypted);
    }

    public function test_plaintext_purge_requires_verified_mirrors_and_keeps_model_reads_working(): void
    {
        $migration = require base_path('database/migrations/2026_10_05_000000_add_encrypted_student_medical_history_columns.php');
        $migration->up();

        $profile = HealthProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'medical_history' => ['Asthma'],
            'other_illness' => 'None',
            'food_allergies' => 'Peanuts',
            'medicine_allergies' => ['Aspirin'],
            'other_med_allergies' => 'None',
            'vaccine_history' => ['brand' => 'Example'],
        ]);

        $this->assertSame(0, Artisan::call('health:encrypt-student-medical-history', ['--apply' => true]));
        Config::set('health_data_encryption.enabled', true);

        $this->assertSame(0, Artisan::call('health:purge-plaintext-health-data'));
        $this->assertNotNull(DB::table('health_profiles')->where('id', $profile->id)->value('medical_history'));

        $this->assertSame(0, Artisan::call('health:purge-plaintext-health-data', ['--apply' => true]));

        $raw = DB::table('health_profiles')->where('id', $profile->id)->first();
        $this->assertNull($raw->medical_history);
        $this->assertNull($raw->other_illness);
        $this->assertNull($raw->food_allergies);

        $fresh = HealthProfile::withoutGlobalScopes()->findOrFail($profile->id);
        $this->assertSame(['Asthma'], $fresh->medical_history);
        $this->assertSame('None', $fresh->other_illness);
        $this->assertSame('Peanuts', $fresh->food_allergies);
        $this->assertSame(['Aspirin'], $fresh->medicine_allergies);
        $this->assertTrue($fresh->hasMedicalCondition());
    }

    public function test_plaintext_purge_keeps_source_when_ciphertext_cannot_be_verified(): void
    {
        $migration = require base_path('database/migrations/2026_10_05_000000_add_encrypted_student_medical_history_columns.php');
        $migration->up();

        $profile = HealthProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'medical_history' => ['Asthma'],
        ]);

        $this->assertSame(0, Artisan::call('health:encrypt-student-medical-history', ['--apply' => true]));
        Config::set('health_data_encryption.enabled', true);
        DB::table('health_profiles')->where('id', $profile->id)->update([
            'medical_history_encrypted' => 'not-valid-ciphertext',
        ]);

        $this->assertSame(1, Artisan::call('health:purge-plaintext-health-data', ['--apply' => true]));
        $this->assertNotNull(DB::table('health_profiles')->where('id', $profile->id)->value('medical_history'));
    }

    public function test_plaintext_purge_accepts_an_integer_source_matching_its_decrypted_string_mirror(): void
    {
        $migration = require base_path('database/migrations/2026_10_05_000000_add_encrypted_student_medical_history_columns.php');
        $migration->up();
        $profileFieldsMigration = require base_path('database/migrations/2026_10_08_000001_add_encrypted_health_profile_fields.php');
        $profileFieldsMigration->up();

        $profile = HealthProfile::withoutGlobalScopes()->create([
            'user_id' => null,
            'age' => 42,
        ]);

        DB::table('health_profiles')->where('id', $profile->id)->update([
            'age_encrypted' => Crypt::encryptString('42'),
        ]);
        Config::set('health_data_encryption.enabled', true);

        $this->assertSame(0, Artisan::call('health:purge-plaintext-health-data'));
        $this->assertSame(42, DB::table('health_profiles')->where('id', $profile->id)->value('age'));

        $this->assertSame(0, Artisan::call('health:purge-plaintext-health-data', ['--apply' => true]));
        $this->assertNull(DB::table('health_profiles')->where('id', $profile->id)->value('age'));
        $this->assertEquals(42, HealthProfile::withoutGlobalScopes()->findOrFail($profile->id)->age);
    }

    private function encryptedColumns(): array
    {
        return [
            'medical_history_encrypted',
            'other_illness_encrypted',
            'food_allergies_encrypted',
            'medicine_allergies_encrypted',
            'other_med_allergies_encrypted',
            'vaccine_history_encrypted',
        ];
    }
}
