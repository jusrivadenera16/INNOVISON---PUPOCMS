<?php

namespace Tests\Feature;

use App\Models\HealthProfile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
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
