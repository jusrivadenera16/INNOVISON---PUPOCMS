<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HealthDataEncryptionPrototypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('encryption_prototype_health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->text('medical_history')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->text('medical_remarks')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('encryption_prototype_health_profiles');

        parent::tearDown();
    }

    public function test_sensitive_values_are_encrypted_at_rest_and_decrypted_by_the_model(): void
    {
        $profile = new EncryptionPrototypeHealthProfile([
            'medical_history' => ['Asthma', 'Food allergy'],
            'emergency_contact' => 'Juan Dela Cruz - 09123456789',
            'medical_remarks' => 'Requires follow-up review.',
        ]);
        $profile->save();

        $raw = DB::table('encryption_prototype_health_profiles')->where('id', $profile->id)->first();

        $this->assertIsObject($raw);
        $this->assertIsString($raw->medical_history);
        $this->assertIsString($raw->emergency_contact);
        $this->assertIsString($raw->medical_remarks);
        $this->assertStringNotContainsString('Asthma', $raw->medical_history);
        $this->assertStringNotContainsString('Juan Dela Cruz', $raw->emergency_contact);
        $this->assertStringNotContainsString('follow-up review', $raw->medical_remarks);

        $fresh = EncryptionPrototypeHealthProfile::query()->findOrFail($profile->id);

        $this->assertSame(['Asthma', 'Food allergy'], $fresh->medical_history);
        $this->assertSame('Juan Dela Cruz - 09123456789', $fresh->emergency_contact);
        $this->assertSame('Requires follow-up review.', $fresh->medical_remarks);
    }

    public function test_encrypted_array_and_nullable_values_round_trip(): void
    {
        $profile = EncryptionPrototypeHealthProfile::query()->create([
            'medical_history' => [],
            'emergency_contact' => null,
            'medical_remarks' => '',
        ]);

        $fresh = EncryptionPrototypeHealthProfile::query()->findOrFail($profile->id);

        $this->assertSame([], $fresh->medical_history);
        $this->assertNull($fresh->emergency_contact);
        $this->assertSame('', $fresh->medical_remarks);
    }
}

class EncryptionPrototypeHealthProfile extends Model
{
    protected $table = 'encryption_prototype_health_profiles';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'medical_history' => 'encrypted:array',
        'emergency_contact' => 'encrypted',
        'medical_remarks' => 'encrypted',
    ];
}
