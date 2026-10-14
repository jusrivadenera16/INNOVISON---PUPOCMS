<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EncryptedHealthSourceNullabilityMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->boolean('no_allergies')->default(false);
        });

        Schema::create('health_profile_emp', function (Blueprint $table): void {
            $table->id();
            foreach ([
                'previous_hospitalization',
                'operation_surgery',
                'cigarette_smoking',
                'alcohol_drinking',
                'traveled_abroad',
                'has_disability',
            ] as $column) {
                $table->boolean($column)->default(false);
            }
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('health_profile_emp');
        Schema::dropIfExists('health_profiles');

        parent::tearDown();
    }

    public function test_mapped_boolean_sources_can_be_null_but_rollback_refuses_to_discard_that_state(): void
    {
        $migration = require base_path('database/migrations/2026_10_12_060000_allow_null_encrypted_health_source_fields.php');
        $migration->up();

        DB::table('health_profiles')->insert(['no_allergies' => null]);
        DB::table('health_profile_emp')->insert([
            'previous_hospitalization' => null,
            'operation_surgery' => null,
            'cigarette_smoking' => null,
            'alcohol_drinking' => null,
            'traveled_abroad' => null,
            'has_disability' => null,
        ]);

        try {
            $migration->down();
            $this->fail('Rollback must not make encrypted-only rows invalid.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('encrypted-only records contain NULL', $exception->getMessage());
        }
    }
}
