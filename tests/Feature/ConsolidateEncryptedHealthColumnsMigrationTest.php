<?php

namespace Tests\Feature;

use App\Models\ConsultationDraft;
use App\Models\DependentsProfile;
use App\Models\HealthProfile;
use App\Services\HealthDataEncrypter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ConsolidateEncryptedHealthColumnsMigrationTest extends TestCase
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
            $table->string('home_address')->nullable();
            $table->json('medical_history')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('home_address_encrypted')->nullable();
            $table->text('medical_history_encrypted')->nullable();
            $table->text('age_encrypted')->nullable();
        });

        Schema::create('dependents_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('idp_user_id')->nullable();
            $table->string('id_number')->nullable();
            $table->text('idp_user_id_encrypted')->nullable();
            $table->text('id_number_encrypted')->nullable();
            $table->index('idp_user_id', 'dependents_profiles_idp_user_id_index');
            $table->index('id_number', 'dependents_profiles_id_number_index');
        });

        Schema::create('consultation_drafts', function (Blueprint $table): void {
            $table->id();
            $table->longText('payload');
            $table->longText('payload_encrypted')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('consultation_drafts');
        Schema::dropIfExists('dependents_profiles');
        Schema::dropIfExists('health_profiles');

        parent::tearDown();
    }

    public function test_migration_moves_ciphertext_into_original_columns_and_rollback_restores_mirrors(): void
    {
        $encrypter = app(HealthDataEncrypter::class);
        $profileId = DB::table('health_profiles')->insertGetId([
            'home_address_encrypted' => $encrypter->encrypt('123 Private Street'),
            'medical_history_encrypted' => $encrypter->encrypt(json_encode(['Asthma'], JSON_THROW_ON_ERROR)),
            'age_encrypted' => $encrypter->encrypt('42'),
        ]);
        $dependentId = DB::table('dependents_profiles')->insertGetId([
            'idp_user_id_encrypted' => $encrypter->encrypt('dependent-123'),
            'id_number_encrypted' => $encrypter->encrypt('D-123456'),
        ]);
        $draftPayload = ['remarks' => 'Sensitive clinical note'];
        $draftId = DB::table('consultation_drafts')->insertGetId([
            'payload' => '[]',
            'payload_encrypted' => $encrypter->encrypt($draftPayload, true),
        ]);

        $migration = $this->migration();
        $migration->up();

        foreach ([
            'health_profiles' => ['home_address_encrypted', 'medical_history_encrypted', 'age_encrypted'],
            'dependents_profiles' => ['idp_user_id_encrypted', 'id_number_encrypted'],
            'consultation_drafts' => ['payload_encrypted'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertFalse(Schema::hasColumn($table, $column), "{$table}.{$column} should be removed after consolidation.");
            }
        }

        $rawProfile = DB::table('health_profiles')->where('id', $profileId)->first();
        $this->assertSame('123 Private Street', $encrypter->decrypt($rawProfile->home_address));
        $this->assertSame(['Asthma'], json_decode($encrypter->decrypt($rawProfile->medical_history), true));
        $this->assertSame('42', $encrypter->decrypt($rawProfile->age));

        $profile = HealthProfile::withoutGlobalScopes()->findOrFail($profileId);
        $this->assertSame('123 Private Street', $profile->home_address);
        $this->assertSame(['Asthma'], $profile->medical_history);
        $this->assertSame(42, $profile->age);

        $dependent = DependentsProfile::withoutGlobalScopes()->findOrFail($dependentId);
        $this->assertSame('dependent-123', $dependent->idp_user_id);
        $this->assertSame('D-123456', $dependent->id_number);
        $this->assertSame($draftPayload, ConsultationDraft::withoutGlobalScopes()->findOrFail($draftId)->payload);
        $this->assertNotContains('dependents_profiles_idp_user_id_index', Schema::getIndexListing('dependents_profiles'));
        $this->assertNotContains('dependents_profiles_id_number_index', Schema::getIndexListing('dependents_profiles'));

        $migration->down();

        $this->assertTrue(Schema::hasColumn('health_profiles', 'home_address_encrypted'));
        $this->assertTrue(Schema::hasColumn('consultation_drafts', 'payload_encrypted'));
        $restoredIndexes = Schema::getIndexListing('dependents_profiles');
        $this->assertContains('dependents_profiles_idp_user_id_index', $restoredIndexes);
        $this->assertContains('dependents_profiles_id_number_index', $restoredIndexes);

        $restoredProfile = DB::table('health_profiles')->where('id', $profileId)->first();
        $this->assertNull($restoredProfile->home_address);
        $this->assertSame('123 Private Street', $encrypter->decrypt($restoredProfile->home_address_encrypted));
        $this->assertSame(['Asthma'], json_decode($encrypter->decrypt($restoredProfile->medical_history_encrypted), true));
        $this->assertSame('42', $encrypter->decrypt($restoredProfile->age_encrypted));

        $restoredDraft = DB::table('consultation_drafts')->where('id', $draftId)->first();
        $this->assertSame([], json_decode($restoredDraft->payload, true));
        $this->assertSame($draftPayload, $encrypter->decrypt($restoredDraft->payload_encrypted, true));
    }

    public function test_migration_stops_before_changes_when_original_field_is_populated(): void
    {
        DB::table('health_profiles')->insert([
            'home_address' => 'Unexpected plaintext',
            'home_address_encrypted' => app(HealthDataEncrypter::class)->encrypt('Encrypted address'),
        ]);

        try {
            $this->migration()->up();
            $this->fail('Expected migration to stop on conflicting source data.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Original column health_profiles.home_address is occupied', $exception->getMessage());
        }

        $row = DB::table('health_profiles')->first();
        $this->assertSame('Unexpected plaintext', $row->home_address);
        $this->assertNotNull($row->home_address_encrypted);
        $this->assertTrue(Schema::hasColumn('consultation_drafts', 'payload_encrypted'));
        $this->assertContains('dependents_profiles_idp_user_id_index', Schema::getIndexListing('dependents_profiles'));
    }

    public function test_migration_stops_before_changes_when_an_encrypted_mirror_is_indexed(): void
    {
        Schema::table('health_profiles', function (Blueprint $table): void {
            $table->index('home_address_encrypted', 'health_profiles_home_address_encrypted_index');
        });

        try {
            $this->migration()->up();
            $this->fail('Expected migration to stop when an encrypted mirror has an index.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Index health_profiles_home_address_encrypted_index', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasColumn('health_profiles', 'home_address_encrypted'));
        $this->assertContains('health_profiles_home_address_encrypted_index', Schema::getIndexListing('health_profiles'));
        $this->assertContains('dependents_profiles_idp_user_id_index', Schema::getIndexListing('dependents_profiles'));
    }

    private function migration(): Migration
    {
        return require base_path('database/migrations/2026_10_27_000001_consolidate_encrypted_health_columns.php');
    }
}
