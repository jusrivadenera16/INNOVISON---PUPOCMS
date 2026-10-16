<?php

namespace Tests\Feature;

use App\Models\ConsultationDraft;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsultationDraftPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('consultation_drafts', function (Blueprint $table): void {
            $table->id();
            $table->json('payload');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('consultation_drafts');

        parent::tearDown();
    }

    public function test_model_encrypts_new_payloads_and_keeps_plaintext_column_empty(): void
    {
        $draft = new ConsultationDraft();
        $draft->payload = ['remarks' => 'Sensitive clinical note'];

        $attributes = $draft->getAttributes();

        $this->assertSame(['remarks' => 'Sensitive clinical note'], $draft->payload);
        $this->assertSame([], json_decode($attributes['payload'], true));
        $this->assertNotSame('Sensitive clinical note', $attributes['payload_encrypted']);
        $this->assertSame(['remarks' => 'Sensitive clinical note'], Crypt::decrypt($attributes['payload_encrypted']));
        $this->assertArrayNotHasKey('payload', $draft->toArray());
        $this->assertArrayNotHasKey('payload_encrypted', $draft->toArray());
    }

    public function test_migration_adds_and_removes_encrypted_payload_column(): void
    {
        $migration = $this->migration();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('consultation_drafts', 'payload_encrypted'));

        $payload = ['remarks' => 'Sensitive clinical note'];
        $id = DB::table('consultation_drafts')->insertGetId([
            'payload' => '[]',
            'payload_encrypted' => Crypt::encrypt($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration->down();

        $this->assertFalse(Schema::hasColumn('consultation_drafts', 'payload_encrypted'));
        $this->assertSame($payload, json_decode(DB::table('consultation_drafts')->where('id', $id)->value('payload'), true));
    }

    public function test_backfill_dry_run_does_not_write_and_apply_encrypts_then_clears_legacy_json(): void
    {
        $this->migration()->up();
        $payload = ['bp' => '120/80', 'remarks' => 'Sensitive clinical note'];
        $id = DB::table('consultation_drafts')->insertGetId([
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, Artisan::call('consultation-drafts:encrypt'));
        $this->assertNull(DB::table('consultation_drafts')->where('id', $id)->value('payload_encrypted'));

        $this->assertSame(0, Artisan::call('consultation-drafts:encrypt', ['--apply' => true]));
        $raw = DB::table('consultation_drafts')->where('id', $id)->first();

        $this->assertSame([], json_decode($raw->payload, true));
        $this->assertNotSame(json_encode($payload), $raw->payload_encrypted);
        $this->assertSame($payload, Crypt::decrypt($raw->payload_encrypted));
    }

    public function test_cleanup_expires_drafts_one_day_after_their_latest_save(): void
    {
        $id = DB::table('consultation_drafts')->insertGetId([
            'payload' => '[]',
            'created_at' => now()->subDays(90),
            'updated_at' => now()->subDays(2),
        ]);
        $nullUpdatedAtId = DB::table('consultation_drafts')->insertGetId([
            'payload' => '[]',
            'created_at' => now()->subDays(90),
            'updated_at' => null,
        ]);
        $recentId = DB::table('consultation_drafts')->insertGetId([
            'payload' => '[]',
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);
        $resavedId = DB::table('consultation_drafts')->insertGetId([
            'payload' => '[]',
            'created_at' => now()->subDays(90),
            'updated_at' => now(),
        ]);

        Config::set('consultation_drafts.retention_days', 0);
        $this->assertSame(0, Artisan::call('consultation-drafts:cleanup'));
        $this->assertDatabaseHas('consultation_drafts', ['id' => $id]);

        Config::set('consultation_drafts.retention_days', 1);
        $this->assertSame(0, Artisan::call('consultation-drafts:cleanup'));
        $this->assertDatabaseMissing('consultation_drafts', ['id' => $id]);
        $this->assertDatabaseMissing('consultation_drafts', ['id' => $nullUpdatedAtId]);
        $this->assertDatabaseHas('consultation_drafts', ['id' => $recentId]);
        $this->assertDatabaseHas('consultation_drafts', ['id' => $resavedId]);
    }

    private function migration()
    {
        return require base_path('database/migrations/2026_10_16_000001_add_encrypted_payload_to_consultation_drafts.php');
    }
}
